package com.roxcode.leestravelcruises.ui.view

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.bumptech.glide.Glide
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.databinding.ActivityPromoDetailBinding
import com.roxcode.leestravelcruises.utils.Constants
import java.net.URLEncoder

class PromoDetailActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPromoDetailBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPromoDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setupToolbar()
        loadPromoData()
    }

    private fun setupToolbar() {
        binding.includeToolbar.btnHeaderAction.setImageResource(R.drawable.ic_arrow_back)
        binding.includeToolbar.btnHeaderAction.setOnClickListener {
            finish()
        }
    }

    private fun loadPromoData() {
        val title = intent.getStringExtra("PROMO_TITLE") ?: ""
        val desc = intent.getStringExtra("PROMO_DESC") ?: ""
        val dest = intent.getStringExtra("PROMO_DEST") ?: ""
        val price = intent.getStringExtra("PROMO_PRICE")
        val image = intent.getStringExtra("PROMO_IMAGE")

        binding.tvPromoDetailTitle.text = title
        binding.tvPromoDetailDesc.text = desc
        binding.tvPromoDetailDest.text = dest

        if (price != null && price != "null") {
            binding.tvPromoDetailPrice.text = "$ $price USD"
        } else {
            binding.tvPromoDetailPrice.visibility = android.view.View.GONE
        }

        if (!image.isNullOrEmpty()) {
            val fullUrl = Constants.IMAGE_BASE_URL + image.replace("\\", "/")
            Glide.with(this)
                .load(fullUrl)
                .centerCrop()
                .into(binding.ivPromoDetailBanner)
        }

        binding.btnContactWhatsApp.setOnClickListener {
            openWhatsApp(title)
        }
    }

    private fun openWhatsApp(promoTitle: String) {
        val phone = "51906066682"
        val message = "Hola Lees Travel, me interesa la promoción: *$promoTitle* y me gustaría recibir más información."

        try {
            val intent = Intent(Intent.ACTION_VIEW)
            val url = "https://api.whatsapp.com/send?phone=$phone&text=" + URLEncoder.encode(message, "UTF-8")
            intent.data = Uri.parse(url)
            startActivity(intent)
        } catch (e: Exception) {
            Toast.makeText(this, "WhatsApp no está instalado", Toast.LENGTH_SHORT).show()
        }
    }
}