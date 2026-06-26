package com.roxcode.leestravelcruises.ui.adapter

import android.graphics.Color
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.data.model.PublicationPost
import com.roxcode.leestravelcruises.databinding.ItemPublicationBinding
import com.roxcode.leestravelcruises.utils.Constants
import java.text.SimpleDateFormat
import java.util.Locale

class PublicationAdapter(
    private var publications: List<PublicationPost>,
    private val onPublicationClick: (PublicationPost) -> Unit
) : RecyclerView.Adapter<PublicationAdapter.PublicationViewHolder>() {

    private var originalPublications: List<PublicationPost> = publications.toList()

    fun updateData(newPublications: List<PublicationPost>) {
        this.publications = newPublications
        this.originalPublications = newPublications.toList()
        notifyDataSetChanged()
    }

    fun filter(query: String) {
        val filteredList = if (query.isEmpty()) {
            originalPublications
        } else {
            originalPublications.filter { post ->
                post.title.contains(query, ignoreCase = true) ||
                        post.moderationStatus.contains(query, ignoreCase = true)
            }
        }
        this.publications = filteredList
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): PublicationViewHolder {
        val binding = ItemPublicationBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return PublicationViewHolder(binding)
    }

    override fun onBindViewHolder(holder: PublicationViewHolder, position: Int) {
        holder.bind(publications[position])
    }

    override fun getItemCount(): Int = publications.size

    inner class PublicationViewHolder(private val binding: ItemPublicationBinding) : RecyclerView.ViewHolder(binding.root) {
        fun bind(post: PublicationPost) {
            binding.tvPublicationTitle.text = post.title
            binding.tvMediaCount.text = post.gallery.size.toString()
            binding.tvStatusBadge.text = post.moderationStatus

            val rawDate = post.latitude ?: ""
            val formattedDate = try {
                val parser = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault())
                val formatter = SimpleDateFormat("dd/MM/yyyy", Locale.getDefault())
                val date = parser.parse(rawDate)
                if (date != null) formatter.format(date) else "Sin fecha"
            } catch (e: Exception) {
                if (rawDate.length >= 10) rawDate.take(10) else "Sin fecha"
            }
            binding.tvPublicationDate.text = formattedDate

            when (post.moderationStatus.lowercase()) {
                "aprobado", "publicado" -> {
                    binding.tvStatusBadge.setBackgroundResource(R.drawable.bg_badge_outline_blue)
                    binding.tvStatusBadge.setTextColor(Color.parseColor("#0000FF"))
                }
                "borrador", "pendiente" -> {
                    binding.tvStatusBadge.setBackgroundResource(R.drawable.bg_badge_outline_orange)
                    binding.tvStatusBadge.setTextColor(Color.parseColor("#FFA000"))
                }
                else -> {
                    binding.tvStatusBadge.setBackgroundResource(R.drawable.bg_badge_outline_gray)
                    binding.tvStatusBadge.setTextColor(Color.parseColor("#757575"))
                }
            }

            if (post.gallery.isNotEmpty()) {
                val path = post.gallery[0].replace("\\", "/")
                val imageUrl = Constants.IMAGE_BASE_URL + path
                Glide.with(binding.root.context)
                    .load(imageUrl)
                    .centerCrop()
                    .placeholder(android.R.drawable.ic_menu_gallery)
                    .into(binding.ivPublicationThumbnail)
            } else {
                binding.ivPublicationThumbnail.setImageResource(android.R.drawable.ic_menu_gallery)
            }

            binding.root.setOnClickListener {
                onPublicationClick(post)
            }
        }
    }
}