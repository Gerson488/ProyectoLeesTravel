package com.roxcode.leestravelcruises.ui.view

import android.content.Intent
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.bumptech.glide.Glide
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.roxcode.leestravelcruises.databinding.ActivityMediaItemBinding

class MediaItemActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMediaItemBinding
    private var mediaUri: Uri? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMediaItemBinding.inflate(layoutInflater)
        setContentView(binding.root)

        mediaUri = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            intent.getParcelableExtra("MEDIA_URI", Uri::class.java)
        } else {
            @Suppress("DEPRECATION")
            intent.getParcelableExtra("MEDIA_URI")
        }

        setupToolbar()
        loadMainDetailImage()
        setupListeners()
    }

    private fun setupToolbar() {
        binding.tvHeaderFormTitle.text = "Fotos y Videos"
        binding.btnBackHeader.text = "Regresar"
        binding.btnBackHeader.setOnClickListener {
            setResult(RESULT_CANCELED)
            finish()
        }
    }

    private fun loadMainDetailImage() {
        mediaUri?.let { uri ->
            Glide.with(this)
                .load(uri)
                .into(binding.ivDetailMainImage)
        } ?: run {
            Toast.makeText(this, "No se proporcionó una imagen válida", Toast.LENGTH_SHORT).show()
            finish()
        }
    }

    private fun setupListeners() {
        binding.btnBorrar.setOnClickListener {
            MaterialAlertDialogBuilder(this)
                .setTitle("Quitar imagen")
                .setMessage("¿Deseas quitar esta imagen de la lista?")
                .setNegativeButton("Cancelar", null)
                .setPositiveButton("Quitar") { _, _ ->
                    mediaUri?.let { uriToDelete ->
                        val returnIntent = Intent().apply {
                            putExtra("MEDIA_URI_TO_DELETE", uriToDelete)
                        }
                        setResult(RESULT_OK, returnIntent)
                        finish()
                    }
                }
                .show()
        }
    }
}