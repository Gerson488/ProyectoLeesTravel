package com.roxcode.leestravelcruises.ui.adapter

import android.graphics.Color
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.data.model.Trip
import com.roxcode.leestravelcruises.databinding.ItemTripBinding
import com.roxcode.leestravelcruises.utils.Constants

class TripAdapter(
    private var trips: List<Trip> = emptyList(),
    private val onItemClick: (Trip) -> Unit
) : RecyclerView.Adapter<TripAdapter.TripViewHolder>() {

    class TripViewHolder(val binding: ItemTripBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): TripViewHolder {
        val binding = ItemTripBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return TripViewHolder(binding)
    }

    override fun onBindViewHolder(holder: TripViewHolder, position: Int) {
        val trip = trips[position]

        holder.itemView.setOnClickListener { onItemClick(trip) }

        holder.binding.apply {
            tvBadgeDestino.text = trip.destination
            tvBadgeFecha.text = trip.departureDate
            tvTripTitle.text = "${trip.origin} - ${trip.destination}"
            tvTripSubtitle.text = "Barco: ${trip.shipName}"
            val estadoViaje = trip.tripStatus
            tvBadgeEstado.text = estadoViaje
            when (estadoViaje) {
                "Programado" -> {
                    tvBadgeEstado.setBackgroundResource(R.drawable.shape_badge_blue)
                    tvBadgeEstado.setTextColor(Color.WHITE)
                }
                "En Curso" -> {
                    tvBadgeEstado.setBackgroundResource(R.drawable.shape_badge_yellow)
                    tvBadgeEstado.setTextColor(Color.parseColor("#212121"))
                }
                "Finalizado" -> {
                    tvBadgeEstado.setBackgroundResource(R.drawable.shape_badge_light_blue)
                    tvBadgeEstado.setTextColor(Color.WHITE)
                }
                "Cancelado" -> {
                    tvBadgeEstado.setBackgroundColor(Color.parseColor("#FFCDD2"))
                    tvBadgeEstado.setTextColor(Color.parseColor("#C62828"))
                }
                else -> {
                    tvBadgeEstado.setBackgroundColor(Color.parseColor("#E0E0E0"))
                    tvBadgeEstado.setTextColor(Color.BLACK)
                }
            }

            val baseUrl = Constants.IMAGE_BASE_URL
            val imageUrl = if (!trip.tripPhoto.isNullOrEmpty()) {
                val cleanPath = trip.tripPhoto.replace("\\", "/")
                baseUrl + cleanPath
            } else {
                null
            }

            Glide.with(root.context)
                .load(imageUrl)
                .placeholder(R.drawable.bg_cruise)
                .error(R.drawable.bg_cruise)
                .into(ivTripPhoto)
        }
    }

    override fun getItemCount(): Int = trips.size

    fun updateTrips(newTrips: List<Trip>) {
        this.trips = newTrips
        notifyDataSetChanged()
    }
}