package com.roxcode.leestravelcruises.ui.view

import android.Manifest
import android.annotation.SuppressLint
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.location.Geocoder
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.os.Environment
import android.view.View
import android.widget.Toast
import androidx.activity.OnBackPressedCallback
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.core.content.FileProvider
import androidx.core.widget.addTextChangedListener
import androidx.recyclerview.widget.LinearLayoutManager
import com.google.android.gms.location.LocationServices
import com.google.android.material.chip.Chip
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.google.gson.Gson
import com.roxcode.leestravelcruises.databinding.ActivityViewPublicationBinding
import com.roxcode.leestravelcruises.ui.adapter.MediaPreviewAdapter
import com.roxcode.leestravelcruises.ui.viewmodel.ViewPublicationViewModel
import com.roxcode.leestravelcruises.utils.Constants
import com.roxcode.leestravelcruises.utils.SessionManager
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.File
import java.io.FileOutputStream
import java.util.Locale

class ViewPublicationActivity : AppCompatActivity() {

    private lateinit var binding: ActivityViewPublicationBinding
    private val viewModel: ViewPublicationViewModel by viewModels()
    private lateinit var mediaAdapter: MediaPreviewAdapter
    private lateinit var sessionManager: SessionManager
    private var photoUri: Uri? = null
    private var currentGpsLocation: String = "0.0,0.0"
    private var isEditMode = false
    private var postId: String? = null
    private var hasChanges = false

    private val takePhotoLauncher = registerForActivityResult(ActivityResultContracts.TakePicture()) { success ->
        if (success) {
            photoUri?.let { mediaAdapter.addMedia(it) }
            hasChanges = true
        }
    }

    private val pickGalleryLauncher = registerForActivityResult(ActivityResultContracts.GetContent()) { uri ->
        uri?.let {
            mediaAdapter.addMedia(it)
            hasChanges = true
        }
    }

    private val requestCameraPermissionLauncher = registerForActivityResult(ActivityResultContracts.RequestPermission()) { isGranted ->
        if (isGranted) openCamera() else Toast.makeText(this, "Permiso de cámara denegado", Toast.LENGTH_SHORT).show()
    }

    private val requestLocationPermissionLauncher = registerForActivityResult(ActivityResultContracts.RequestPermission()) { isGranted ->
        if (isGranted) getLastLocation() else Toast.makeText(this, "Permiso de ubicación denegado", Toast.LENGTH_SHORT).show()
    }

    private val mediaItemLauncher = registerForActivityResult(ActivityResultContracts.StartActivityForResult()) { result ->
        if (result.resultCode == RESULT_OK) {
            val data = result.data
            val uriToDelete = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
                data?.getParcelableExtra("MEDIA_URI_TO_DELETE", Uri::class.java)
            } else {
                @Suppress("DEPRECATION")
                data?.getParcelableExtra("MEDIA_URI_TO_DELETE")
            }
            uriToDelete?.let {
                mediaAdapter.removeMedia(it)
                hasChanges = true
            }
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityViewPublicationBinding.inflate(layoutInflater)
        setContentView(binding.root)

        sessionManager = SessionManager(this)

        postId = intent.getStringExtra("POST_ID")
        isEditMode = !postId.isNullOrEmpty()

        onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                showExitConfirmationDialog()
            }
        })

        setupRecyclerView()
        setupUI()
        setupObservers()
        setupListeners()
        checkLocationPermission()
    }

    private fun setupUI() {
        if (isEditMode) {
            binding.tvFormTitle.text = "Editar Publicación"
            binding.btnSaveEvent.text = "Actualizar Cambios"
            binding.etTravelTitle.setText(intent.getStringExtra("TITLE"))
            binding.etTravelComment.setText(intent.getStringExtra("DESC"))
            binding.btnDeleteEvent.visibility = View.VISIBLE

            val savedGallery = intent.getStringArrayListExtra("GALLERY")
            savedGallery?.forEach { path ->
                val fullUrl = if (path.startsWith("http")) path
                else Constants.IMAGE_BASE_URL + path.replace("\\", "/")
                mediaAdapter.addMedia(Uri.parse(fullUrl))
            }
        }

        binding.etTravelTitle.addTextChangedListener { hasChanges = true }
        binding.etTravelComment.addTextChangedListener { hasChanges = true }
    }

    private fun setupObservers() {
        viewModel.statusMessage.observe(this) { message ->
            when (message) {
                "SUCCESS", "SUCCESS_UPDATE", "SUCCESS_DELETE" -> {
                    Toast.makeText(this, "Operación completada", Toast.LENGTH_SHORT).show()
                    hasChanges = false
                    finish()
                }
                null -> {}
                else -> {
                    binding.btnSaveEvent.isEnabled = true
                    binding.btnSaveEvent.text = if (isEditMode) "Actualizar Cambios" else "Guardar evento"
                    Toast.makeText(this, message, Toast.LENGTH_LONG).show()
                }
            }
        }
    }

    private fun showExitConfirmationDialog() {
        if (hasChanges) {
            MaterialAlertDialogBuilder(this)
                .setTitle("Cambios sin guardar")
                .setMessage("Tienes cambios pendientes. ¿Estás seguro de que quieres salir?")
                .setNegativeButton("Cancelar", null)
                .setPositiveButton("Salir") { _, _ -> finish() }
                .show()
        } else {
            finish()
        }
    }

    private fun setupListeners() {
        binding.btnBackHeader.setOnClickListener { showExitConfirmationDialog() }

        binding.btnTakePhoto.setOnClickListener { showImageSourceDialog() }
        binding.btnSaveEvent.setOnClickListener { performAction() }

        binding.btnDeleteEvent.setOnClickListener {
            MaterialAlertDialogBuilder(this)
                .setTitle("Eliminar publicación")
                .setMessage("¿Estás seguro de eliminar este registro y sus fotos?")
                .setNegativeButton("Cancelar", null)
                .setPositiveButton("Eliminar") { _, _ ->
                    val userIdActiveInt = sessionManager.getUserId()
                    postId?.let { viewModel.deletePublication(it, userIdActiveInt) }
                }
                .show()
        }

        for (i in 0 until binding.cgSuggestions.childCount) {
            val chip = binding.cgSuggestions.getChildAt(i) as? Chip
            chip?.setOnClickListener { binding.etTravelTitle.setText(chip.text) }
        }
    }

    private fun performAction() {
        val title = binding.etTravelTitle.text.toString()
        if (title.isEmpty()) {
            Toast.makeText(this, "El título es necesario", Toast.LENGTH_SHORT).show()
            return
        }

        binding.btnSaveEvent.isEnabled = false
        binding.btnSaveEvent.text = "Procesando..."
        val userIdActiveString = sessionManager.getUserId().toString()

        val rbIdU = userIdActiveString.toRequestBody("text/plain".toMediaTypeOrNull())
        val rbTitle = title.toRequestBody("text/plain".toMediaTypeOrNull())
        val rbDesc = binding.etTravelComment.text.toString().toRequestBody("text/plain".toMediaTypeOrNull())

        val coords = currentGpsLocation.split(",")
        val rbLat = coords.getOrElse(0) { "0.0" }.toRequestBody("text/plain".toMediaTypeOrNull())
        val rbLng = coords.getOrElse(1) { "0.0" }.toRequestBody("text/plain".toMediaTypeOrNull())

        val images = mediaAdapter.getMediaList().mapNotNull { uri ->
            if (uri.scheme == "content" || uri.scheme == "file") {
                val file = uriToFile(uri)
                if (file != null) {
                    val reqFile = file.asRequestBody("image/jpeg".toMediaTypeOrNull())
                    MultipartBody.Part.createFormData("image[]", file.name, reqFile)
                } else null
            } else null
        }

        if (isEditMode) {
            val rbIdP = postId!!.toRequestBody("text/plain".toMediaTypeOrNull())

            val retainedList = mediaAdapter.getMediaList().filter { uri ->
                uri.scheme == "http" || uri.scheme == "https"
            }.map { uri ->
                uri.toString().replace(Constants.IMAGE_BASE_URL, "")
            }

            val rbRetained = Gson().toJson(retainedList).toRequestBody("text/plain".toMediaTypeOrNull())

            viewModel.updatePublication(rbIdP, rbIdU, rbTitle, rbDesc, rbLat, rbLng, images.ifEmpty { null }, rbRetained)
        } else {
            val tripIdValue = intent.getIntExtra("TRIP_ID", 1).toString()
            val rbIdT = tripIdValue.toRequestBody("text/plain".toMediaTypeOrNull())
            viewModel.registerPublication(rbIdT, rbIdU, rbTitle, rbDesc, rbLat, rbLng, images)
        }
    }

    private fun uriToFile(uri: Uri): File? {
        return try {
            val stream = contentResolver.openInputStream(uri) ?: return null
            val file = File(cacheDir, "temp_img_${System.currentTimeMillis()}.jpg")
            val bitmap = BitmapFactory.decodeStream(stream)
            val out = FileOutputStream(file)
            bitmap.compress(Bitmap.CompressFormat.JPEG, 80, out)
            stream.close()
            out.close()
            file
        } catch (e: Exception) { null }
    }

    private fun showImageSourceDialog() {
        MaterialAlertDialogBuilder(this)
            .setTitle("Seleccionar imagen")
            .setItems(arrayOf("Cámara", "Galería")) { _, which ->
                if (which == 0) checkCameraPermission() else pickGalleryLauncher.launch("image/*")
            }.show()
    }

    private fun checkCameraPermission() {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.CAMERA) == PackageManager.PERMISSION_GRANTED) {
            openCamera()
        } else {
            requestCameraPermissionLauncher.launch(Manifest.permission.CAMERA)
        }
    }

    private fun openCamera() {
        val file = File.createTempFile("IMG_", ".jpg", getExternalFilesDir(Environment.DIRECTORY_PICTURES))
        photoUri = FileProvider.getUriForFile(this, "${packageName}.fileprovider", file)
        takePhotoLauncher.launch(photoUri)
    }

    private fun checkLocationPermission() {
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED) {
            getLastLocation()
        } else {
            requestLocationPermissionLauncher.launch(Manifest.permission.ACCESS_FINE_LOCATION)
        }
    }

    @SuppressLint("MissingPermission")
    private fun getLastLocation() {
        try {
            LocationServices.getFusedLocationProviderClient(this).lastLocation.addOnSuccessListener { location ->
                if (location != null) {
                    currentGpsLocation = "${location.latitude},${location.longitude}"
                    val geocoder = Geocoder(this, Locale.getDefault())
                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
                        geocoder.getFromLocation(location.latitude, location.longitude, 1) { addresses ->
                            if (addresses.isNotEmpty()) {
                                runOnUiThread {
                                    binding.tvCurrentLocation.text = "${addresses[0].locality} - ${addresses[0].adminArea}"
                                }
                            }
                        }
                    } else {
                        @Suppress("DEPRECATION")
                        val addr = geocoder.getFromLocation(location.latitude, location.longitude, 1)
                        if (!addr.isNullOrEmpty()) {
                            binding.tvCurrentLocation.text = "${addr[0].locality} - ${addr[0].adminArea}"
                        }
                    }
                }
            }
        } catch (e: Exception) { }
    }

    private fun setupRecyclerView() {
        mediaAdapter = MediaPreviewAdapter { selectedMediaUri ->
            val intentMediaDetail = Intent(this, MediaItemActivity::class.java).apply {
                putExtra("MEDIA_URI", selectedMediaUri)
            }
            mediaItemLauncher.launch(intentMediaDetail)
        }
        binding.rvMediaPreview.layoutManager = LinearLayoutManager(this, LinearLayoutManager.HORIZONTAL, false)
        binding.rvMediaPreview.adapter = mediaAdapter
    }
}