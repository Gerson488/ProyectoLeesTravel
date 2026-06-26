package com.roxcode.leestravelcruises.ui.adapter

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.data.model.HistoryModel

class HistoryAdapter(private var historyList: List<HistoryModel>) :
    RecyclerView.Adapter<HistoryAdapter.HistoryViewHolder>() {

    class HistoryViewHolder(view: View) : RecyclerView.ViewHolder(view) {
        val tvPassengerName: TextView = view.findViewById(R.id.tvPassengerName)
        val tvDescription: TextView = view.findViewById(R.id.tvDescription)
        val tvDate: TextView = view.findViewById(R.id.tvDate)
        // Agregado el nuevo TextView para el guía
        val tvGuiaName: TextView = view.findViewById(R.id.tvGuiaName)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): HistoryViewHolder {
        // Apunta al nuevo layout con CardView
        val view = LayoutInflater.from(parent.context).inflate(R.layout.item_history_common, parent, false)
        return HistoryViewHolder(view)
    }

    override fun onBindViewHolder(holder: HistoryViewHolder, position: Int) {
        val history = historyList[position]

        holder.tvPassengerName.text = history.passengerName ?: "Pasajero desconocido"
        holder.tvDescription.text = history.eventDescription
        holder.tvDate.text = history.eventDate ?: "Fecha no disponible"

        // Asignación del nombre del guía con un formato legible
        holder.tvGuiaName.text = "Registrado por: ${history.guiaName ?: "Guía"}"
    }

    override fun getItemCount() = historyList.size

    fun updateList(newList: List<HistoryModel>) {
        this.historyList = newList
        notifyDataSetChanged()
    }
}