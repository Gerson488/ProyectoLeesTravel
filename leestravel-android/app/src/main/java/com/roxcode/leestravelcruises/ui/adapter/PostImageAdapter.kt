package com.roxcode.leestravelcruises.ui.adapter

import android.content.Intent
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.roxcode.leestravelcruises.databinding.ItemPostImageBinding
import com.roxcode.leestravelcruises.ui.view.ImagePreviewActivity // Importamos la nueva Activity
import com.roxcode.leestravelcruises.utils.Constants

class PostImageAdapter(private val images: List<String>) :
    RecyclerView.Adapter<PostImageAdapter.ImageViewHolder>() {

    class ImageViewHolder(val binding: ItemPostImageBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ImageViewHolder {
        val binding = ItemPostImageBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return ImageViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ImageViewHolder, position: Int) {
        val path = images[position].replace("\\", "/")
        val fullUrl = Constants.IMAGE_BASE_URL + path

        Glide.with(holder.itemView.context)
            .load(fullUrl)
            .centerCrop()
            .into(holder.binding.ivPostImage)

        holder.itemView.setOnClickListener {
            val context = holder.itemView.context
            val intent = Intent(context, ImagePreviewActivity::class.java).apply {
                putStringArrayListExtra("IMAGES_LIST", ArrayList(images))
                putExtra("START_POSITION", position)
            }
            context.startActivity(intent)
        }
    }

    override fun getItemCount() = images.size
}