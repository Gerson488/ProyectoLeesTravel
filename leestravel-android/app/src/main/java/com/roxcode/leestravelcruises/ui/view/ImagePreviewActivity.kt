package com.roxcode.leestravelcruises.ui.view

import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import androidx.viewpager2.widget.ViewPager2
import com.roxcode.leestravelcruises.databinding.ActivityImagePreviewBinding
import com.roxcode.leestravelcruises.ui.adapter.FullScreenImageAdapter

class ImagePreviewActivity : AppCompatActivity() {

    private lateinit var binding: ActivityImagePreviewBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityImagePreviewBinding.inflate(layoutInflater)
        setContentView(binding.root)

        val imagesList = intent.getStringArrayListExtra("IMAGES_LIST") ?: arrayListOf()
        val startPosition = intent.getIntExtra("START_POSITION", 0)

        if (imagesList.isEmpty()) {
            finish()
            return
        }

        val adapter = FullScreenImageAdapter(imagesList)
        binding.viewPagerFullscreen.adapter = adapter

        binding.viewPagerFullscreen.setCurrentItem(startPosition, false)
        binding.tvPreviewCounter.text = "${startPosition + 1} / ${imagesList.size}"

        binding.viewPagerFullscreen.registerOnPageChangeCallback(object : ViewPager2.OnPageChangeCallback() {
            override fun onPageSelected(position: Int) {
                super.onPageSelected(position)
                binding.tvPreviewCounter.text = "${position + 1} / ${imagesList.size}"
            }
        })

        binding.btnClosePreview.setOnClickListener {
            finish()
        }
    }
}