package com.roxcode.leestravelcruises.ui.view

import android.os.Bundle
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.PagerSnapHelper
import androidx.recyclerview.widget.RecyclerView
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.databinding.ActivityPublicationDetailBinding
import com.roxcode.leestravelcruises.ui.adapter.PostImageAdapter
import com.roxcode.leestravelcruises.ui.viewmodel.PublicFeedViewModel

class PublicationDetailActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPublicationDetailBinding
    private val viewModel: PublicFeedViewModel by viewModels()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPublicationDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)

        val idPublication = intent.getStringExtra("PUBLICATION_ID")

        setupToolbar()
        setupObservers()

        if (!idPublication.isNullOrEmpty()) {
            viewModel.fetchPublicationDetail(idPublication)
        } else {
            Toast.makeText(this, "Publicación no encontrada", Toast.LENGTH_SHORT).show()
            finish()
        }
    }

    private fun setupToolbar() {
        binding.includeToolbar.btnHeaderAction.setImageResource(R.drawable.ic_arrow_back)
        binding.includeToolbar.btnHeaderAction.setOnClickListener {
            finish()
        }
    }

    private fun setupObservers() {
        viewModel.postDetail.observe(this) { publication ->
            if (publication != null) {
                binding.tvDetailTitle.text = publication.title
                binding.tvDetailDescription.text = publication.description
                binding.tvDetailAuthor.text = "Por: ${publication.firstName} ${publication.lastName}"
                binding.tvDetailImageCounter.text = "1/${publication.gallery.size}"

                val imageAdapter = PostImageAdapter(publication.gallery)
                binding.rvDetailImages.layoutManager = LinearLayoutManager(this, LinearLayoutManager.HORIZONTAL, false)
                binding.rvDetailImages.adapter = imageAdapter

                if (binding.rvDetailImages.onFlingListener == null) {
                    PagerSnapHelper().attachToRecyclerView(binding.rvDetailImages)
                }

                binding.rvDetailImages.clearOnScrollListeners()
                binding.rvDetailImages.addOnScrollListener(object : RecyclerView.OnScrollListener() {
                    override fun onScrolled(recyclerView: RecyclerView, dx: Int, dy: Int) {
                        val lm = recyclerView.layoutManager as LinearLayoutManager
                        val pos = lm.findFirstVisibleItemPosition()
                        if (pos != RecyclerView.NO_POSITION) {
                            binding.tvDetailImageCounter.text = "${pos + 1}/${publication.gallery.size}"
                        }
                    }
                })
            }
        }
    }
}