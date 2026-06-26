package com.roxcode.leestravelcruises.ui.adapter

import android.graphics.Color
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.roxcode.leestravelcruises.data.model.PassengerModel
import com.roxcode.leestravelcruises.databinding.ItemAttendancePassengerBinding

class AttendanceAdapter(
    private val onStatusChangeClick: (PassengerModel, String) -> Unit
) : RecyclerView.Adapter<AttendanceAdapter.AttendanceViewHolder>() {

    private var passengerList = mutableListOf<PassengerModel>()
    private var passengerListFull = mutableListOf<PassengerModel>()

    fun updateList(newList: List<PassengerModel>) {
        passengerList = newList.toMutableList()
        passengerListFull = ArrayList(newList)
        notifyDataSetChanged()
    }

    fun filter(query: String) {
        passengerList = if (query.isEmpty()) {
            passengerListFull
        } else {
            passengerListFull.filter {
                it.firstName.contains(query, ignoreCase = true) ||
                        it.lastName.contains(query, ignoreCase = true) ||
                        it.idCardPassport.contains(query, ignoreCase = true)
            }.toMutableList()
        }
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): AttendanceViewHolder {
        val binding = ItemAttendancePassengerBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return AttendanceViewHolder(binding)
    }

    override fun onBindViewHolder(holder: AttendanceViewHolder, position: Int) {
        holder.bind(passengerList[position], onStatusChangeClick)
    }

    override fun getItemCount() = passengerList.size

    class AttendanceViewHolder(
        private val binding: ItemAttendancePassengerBinding
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(passenger: PassengerModel, onStatusChangeClick: (PassengerModel, String) -> Unit) {
            binding.tvPassengerName.text = "${passenger.firstName} ${passenger.lastName}"
            binding.tvPassengerDoc.text = "${passenger.documentType ?: "DOC"}: ${passenger.idCardPassport}"
            val initials = if (passenger.firstName.isNotEmpty() && passenger.lastName.isNotEmpty()) {
                "${passenger.firstName.take(1)}${passenger.lastName.take(1)}".uppercase()
            } else {
                "P"
            }
            binding.tvInitials.text = initials
            val currentStatus = passenger.boardingStatus ?: "Por Abordar"
            binding.tvCurrentStatus.text = currentStatus
            when (currentStatus) {
                "Abordado" -> {
                    binding.tvCurrentStatus.setTextColor(Color.parseColor("#4CAF50"))
                    binding.btnToggleStatus.text = "Ausente"
                    binding.btnToggleStatus.setBackgroundColor(Color.parseColor("#E53935"))
                    binding.btnToggleStatus.setTextColor(Color.WHITE)
                }
                "No Se Presentó", "No Se PresentÃ³" -> {
                    binding.tvCurrentStatus.text = "No Se Presentó"
                    binding.tvCurrentStatus.setTextColor(Color.parseColor("#F44336"))
                    binding.btnToggleStatus.text = "Restablecer"
                    binding.btnToggleStatus.setBackgroundColor(Color.parseColor("#E0E0E0"))
                    binding.btnToggleStatus.setTextColor(Color.parseColor("#212121"))
                }
                else -> {
                    binding.tvCurrentStatus.setTextColor(Color.parseColor("#FF9800"))
                    binding.btnToggleStatus.text = "Abordar"
                    binding.btnToggleStatus.setBackgroundColor(Color.parseColor("#002366"))
                    binding.btnToggleStatus.setTextColor(Color.WHITE)
                }
            }
            binding.btnToggleStatus.setOnClickListener {
                val newStatus = when (currentStatus) {
                    "Por Abordar" -> "Abordado"
                    "Abordado" -> "No Se Presentó"
                    else -> "Por Abordar"
                }
                onStatusChangeClick(passenger, newStatus)
            }
        }
    }
}