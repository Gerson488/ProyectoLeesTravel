package com.roxcode.leestravelcruises.ui.adapter

import android.net.Uri
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.roxcode.leestravelcruises.databinding.ItemMediaPreviewBinding

class MediaPreviewAdapter(
    private val onMediaClick: (Uri) -> Unit
) : RecyclerView.Adapter<MediaPreviewAdapter.MediaViewHolder>() {

    private val mediaList = mutableListOf<Uri>()

    class MediaViewHolder(val binding: ItemMediaPreviewBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): MediaViewHolder {
        val binding = ItemMediaPreviewBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return MediaViewHolder(binding)
    }

    override fun onBindViewHolder(holder: MediaViewHolder, position: Int) {
        val uri = mediaList[position]

        Glide.with(holder.itemView.context)
            .load(uri)
            .centerCrop()
            .into(holder.binding.ivThumbnail)

        holder.binding.root.setOnClickListener {
            onMediaClick(uri)
        }
    }

    override fun getItemCount(): Int = mediaList.size

    fun addMedia(uri: Uri) {
        mediaList.add(uri)
        notifyItemInserted(mediaList.size - 1)
    }

    fun removeMedia(uri: Uri) {
        val index = mediaList.indexOf(uri)
        if (index != -1) {
            mediaList.removeAt(index)
            notifyItemRemoved(index)
        }
    }

    fun getMediaList(): List<Uri> = mediaList

    fun updateData(newList: List<Uri>) {
        mediaList.clear()
        mediaList.addAll(newList)
        notifyDataSetChanged()
    }
}