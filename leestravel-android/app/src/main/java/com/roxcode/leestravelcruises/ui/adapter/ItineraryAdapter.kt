package com.roxcode.leestravelcruises.ui.adapter

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.roxcode.leestravelcruises.data.model.ItineraryModel
import com.roxcode.leestravelcruises.databinding.ItemItineraryBinding

class ItineraryAdapter : RecyclerView.Adapter<ItineraryAdapter.ViewHolder>() {

    private var list = listOf<ItineraryModel>()

    fun updateList(newList: List<ItineraryModel>) {
        list = newList
        notifyDataSetChanged()
    }

    inner class ViewHolder(private val binding: ItemItineraryBinding) : RecyclerView.ViewHolder(binding.root) {
        fun bind(item: ItineraryModel) {
            binding.tvDayNumber.text = "Día ${item.dayNumber}"

            val arr = item.arrivalTime?.substring(0, 5) ?: "--:--"
            val dep = item.departureTime?.substring(0, 5) ?: "--:--"
            binding.tvTimeRange.text = "$arr - $dep"

            binding.tvPortName.text = item.port
            binding.tvActivityDesc.text = item.description
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemItineraryBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return ViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(list[position])
    }

    override fun getItemCount() = list.size
}