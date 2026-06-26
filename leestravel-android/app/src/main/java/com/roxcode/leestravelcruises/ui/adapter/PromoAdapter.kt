package com.roxcode.leestravelcruises.ui.adapter

import android.content.Intent
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.roxcode.leestravelcruises.data.model.PromoPost
import com.roxcode.leestravelcruises.databinding.ItemPromoBannerBinding
import com.roxcode.leestravelcruises.ui.view.PromoDetailActivity
import com.roxcode.leestravelcruises.utils.Constants

class PromoAdapter(
    private var promos: List<PromoPost> = emptyList()
) : RecyclerView.Adapter<PromoAdapter.PromoViewHolder>() {

    class PromoViewHolder(val binding: ItemPromoBannerBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): PromoViewHolder {
        val binding = ItemPromoBannerBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return PromoViewHolder(binding)
    }

    override fun onBindViewHolder(holder: PromoViewHolder, position: Int) {
        val promo = promos[position]
        holder.binding.apply {
            tvPromoTitle.text = promo.titleOffer
            tvPromoDestination.text = "${promo.shipName} - ${promo.destinationName}"

            if (promo.specialPriceUSD != null) {
                tvPromoPrice.text = "$ ${promo.specialPriceUSD}"
                tvPromoPrice.visibility = android.view.View.VISIBLE
            } else {
                tvPromoPrice.visibility = android.view.View.GONE
            }

            promo.imageBanner?.let { banner ->
                val fullUrl = Constants.IMAGE_BASE_URL + banner.replace("\\", "/")
                Glide.with(holder.itemView.context)
                    .load(fullUrl)
                    .centerCrop()
                    .into(ivPromoBanner)
            }

            root.setOnClickListener {
                val context = holder.itemView.context
                val intent = Intent(context, PromoDetailActivity::class.java).apply {
                    putExtra("PROMO_TITLE", promo.titleOffer)
                    putExtra("PROMO_DESC", promo.description)
                    putExtra("PROMO_DEST", "${promo.shipName} - ${promo.destinationName}")
                    putExtra("PROMO_PRICE", promo.specialPriceUSD?.toString())
                    putExtra("PROMO_IMAGE", promo.imageBanner)
                }
                context.startActivity(intent)
            }
        }
    }

    override fun getItemCount() = promos.size

    fun updateData(newList: List<PromoPost>) {
        promos = newList
        notifyDataSetChanged()
    }
}