package com.roxcode.leestravelcruises.ui.adapter

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.roxcode.leestravelcruises.databinding.ItemFullscreenImageBinding
import com.roxcode.leestravelcruises.utils.Constants

class FullScreenImageAdapter(private val images: List<String>) :
    RecyclerView.Adapter<FullScreenImageAdapter.FullscreenViewHolder>() {

    class FullscreenViewHolder(val binding: ItemFullscreenImageBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): FullscreenViewHolder {
        val binding = ItemFullscreenImageBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return FullscreenViewHolder(binding)
    }

    override fun onBindViewHolder(holder: FullscreenViewHolder, position: Int) {
        val path = images[position].replace("\\", "/")
        val fullUrl = Constants.IMAGE_BASE_URL + path

        Glide.with(holder.itemView.context)
            .load(fullUrl)
            .fitCenter()
            .into(holder.binding.ivFullscreenImage)
    }

    override fun getItemCount() = images.size
}