package com.roxcode.leestravelcruises.ui.adapter

import android.graphics.Color
import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.roxcode.leestravelcruises.data.model.PassengerModel
import com.roxcode.leestravelcruises.databinding.ItemPassengerBinding

class PassengerAdapter(
    private val userRole: String, // Recibimos el rol del usuario logueado
    private val onItemClick: (PassengerModel) -> Unit
) : RecyclerView.Adapter<PassengerAdapter.PassengerViewHolder>() {

    private var passengerList = mutableListOf<PassengerModel>()
    private var passengerListFull = mutableListOf<PassengerModel>()

    fun updateList(newList: List<PassengerModel>) {

        val data = newList ?: emptyList()
        passengerList = data.toMutableList()
        passengerListFull = ArrayList(data)
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

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): PassengerViewHolder {
        val binding = ItemPassengerBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return PassengerViewHolder(binding, userRole) // Pasamos el rol al ViewHolder
    }

    override fun onBindViewHolder(holder: PassengerViewHolder, position: Int) {
        val passenger = passengerList[position]
        holder.bind(passenger)
        holder.itemView.setOnClickListener { onItemClick(passenger) }
    }

    override fun getItemCount() = passengerList.size

    class PassengerViewHolder(
        private val binding: ItemPassengerBinding,
        private val userRole: String
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(passenger: PassengerModel) {
            binding.tvPassengerName.text = "${passenger.firstName} ${passenger.lastName}"
            binding.tvPassengerAge.text = "${passenger.age} años"

            // Lógica de visibilidad del DNI/Pasaporte
            val isStaff = userRole.equals("guía", true) ||
                    userRole.equals("guia", true) ||
                    userRole.equals("tripulante", true) ||
                    userRole.equals("admin", true)

            if (isStaff) {
                binding.tvPassengerPhone.visibility = View.VISIBLE
                binding.tvPassengerPhone.text = passenger.idCardPassport
            } else {
                binding.tvPassengerPhone.visibility = View.GONE
            }

            Log.d("DEBUG_ROL", "Usuario: ${passenger.firstName} | Rol recibido: ${passenger.accessRole}")
            val rol = passenger.accessRole ?: "Pasajero"
            val isGuia = rol.equals("Guia", ignoreCase = true) || rol.equals("Guía", ignoreCase = true)

            if (isGuia) {
                binding.tvRole.text = "Guía"
                binding.cardRole.setCardBackgroundColor(Color.parseColor("#E8F5E9"))
                binding.tvRole.setTextColor(Color.parseColor("#2E7D32"))
                binding.cardRole.setStrokeColor(Color.parseColor("#2E7D32"))
            } else {
                binding.tvRole.text = "Pasajero"
                binding.cardRole.setCardBackgroundColor(Color.parseColor("#FFFFFF"))
                binding.tvRole.setTextColor(Color.parseColor("#6200EE"))
                binding.cardRole.setStrokeColor(Color.parseColor("#6200EE"))
            }

            val initials = if (passenger.firstName.isNotEmpty() && passenger.lastName.isNotEmpty()) {
                "${passenger.firstName.take(1)}${passenger.lastName.take(1)}".uppercase()
            } else {
                "P"
            }
            binding.tvInitials.text = initials
        }
    }
}