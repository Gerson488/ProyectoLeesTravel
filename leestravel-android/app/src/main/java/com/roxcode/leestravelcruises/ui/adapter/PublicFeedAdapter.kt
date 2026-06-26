package com.roxcode.leestravelcruises.ui.adapter

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.PagerSnapHelper
import androidx.recyclerview.widget.RecyclerView
import com.roxcode.leestravelcruises.data.model.PublicPublication
import com.roxcode.leestravelcruises.databinding.ItemPublicPublicationBinding

class PublicFeedAdapter(
    private var publications: List<PublicPublication> = emptyList(),
    private val onDetailClick: (PublicPublication) -> Unit
) : RecyclerView.Adapter<PublicFeedAdapter.FeedViewHolder>() {

    class FeedViewHolder(val binding: ItemPublicPublicationBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): FeedViewHolder {
        val binding = ItemPublicPublicationBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return FeedViewHolder(binding)
    }

    override fun onBindViewHolder(holder: FeedViewHolder, position: Int) {
        val pub = publications[position]
        holder.binding.apply {
            tvTitle.text = pub.title
            tvDescription.text = pub.description
            tvAuthor.text = "Por: ${pub.firstName} ${pub.lastName}"
            tvImageCounter.text = "1/${pub.gallery.size}"

            val imageAdapter = PostImageAdapter(pub.gallery)
            rvPostImages.layoutManager = LinearLayoutManager(root.context, LinearLayoutManager.HORIZONTAL, false)
            rvPostImages.adapter = imageAdapter

            if (rvPostImages.onFlingListener == null) {
                PagerSnapHelper().attachToRecyclerView(rvPostImages)
            }

            rvPostImages.clearOnScrollListeners()
            rvPostImages.addOnScrollListener(object : RecyclerView.OnScrollListener() {
                override fun onScrolled(recyclerView: RecyclerView, dx: Int, dy: Int) {
                    val lm = recyclerView.layoutManager as LinearLayoutManager
                    val pos = lm.findFirstVisibleItemPosition()
                    if (pos != RecyclerView.NO_POSITION) {
                        tvImageCounter.text = "${pos + 1}/${pub.gallery.size}"
                    }
                }
            })

            tvReadMore.setOnClickListener { onDetailClick(pub) }
            root.setOnClickListener { onDetailClick(pub) }
        }
    }

    override fun getItemCount() = publications.size

    fun updateData(newList: List<PublicPublication>) {
        publications = newList
        notifyDataSetChanged()
    }
}