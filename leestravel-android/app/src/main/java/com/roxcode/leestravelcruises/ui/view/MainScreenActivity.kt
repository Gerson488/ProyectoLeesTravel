package com.roxcode.leestravelcruises.ui.view

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.widget.Toast
import androidx.activity.OnBackPressedCallback
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.GravityCompat
import androidx.fragment.app.Fragment
import com.google.android.material.tabs.TabLayout
import com.roxcode.leestravelcruises.R
import com.roxcode.leestravelcruises.databinding.ActivityMainScreenBinding
import com.roxcode.leestravelcruises.utils.SessionManager
import java.net.URLEncoder

class MainScreenActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainScreenBinding
    private lateinit var sessionManager: SessionManager
    private var isGuest: Boolean = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainScreenBinding.inflate(layoutInflater)
        setContentView(binding.root)

        sessionManager = SessionManager(this)
        isGuest = intent.getBooleanExtra("IS_GUEST", false)

        setupBackNavigation()
        setupGuestUI()

        if (savedInstanceState == null) {
            if (isGuest) {
                replaceFragment(PublicPublicationFragment())
                binding.tabLayout.getTabAt(1)?.select()
            } else {
                replaceFragment(HomeFragment())
            }
        }

        binding.btnOpenMenu.setOnClickListener {
            binding.drawerLayout.openDrawer(GravityCompat.START)
        }

        binding.tabLayout.addOnTabSelectedListener(object : TabLayout.OnTabSelectedListener {
            override fun onTabSelected(tab: TabLayout.Tab?) {
                when (tab?.position) {
                    0 -> replaceFragment(HomeFragment())
                    1 -> replaceFragment(PublicPublicationFragment())
                }
            }
            override fun onTabUnselected(tab: TabLayout.Tab?) {}
            override fun onTabReselected(tab: TabLayout.Tab?) {}
        })

        binding.navView.setNavigationItemSelectedListener { menuItem ->
            when (menuItem.itemId) {
                R.id.nav_profile -> replaceFragment(ProfileFragment())
                R.id.nav_trips -> replaceFragment(MyTripsFragment())
                R.id.nav_support -> openSupportWhatsApp()
                R.id.nav_logout -> {
                    sessionManager.clear()
                    val intent = Intent(this, LoginActivity::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
                    startActivity(intent)
                    finish()
                }
            }
            binding.drawerLayout.closeDrawer(GravityCompat.START)
            true
        }
    }

    private fun setupGuestUI() {
        if (isGuest) {
            val menu = binding.navView.menu
            menu.findItem(R.id.nav_profile)?.isVisible = false
            menu.findItem(R.id.nav_logout)?.title = "Salir del modo invitado"
        }
    }

    private fun openSupportWhatsApp() {
        val phone = "51906066682"
        val message = "Hola Lees Travel, necesito soporte con la App."
        try {
            val intent = Intent(Intent.ACTION_VIEW)
            val url = "https://api.whatsapp.com/send?phone=$phone&text=" + URLEncoder.encode(message, "UTF-8")
            intent.data = Uri.parse(url)
            startActivity(intent)
        } catch (e: Exception) {
            Toast.makeText(this, "WhatsApp no está instalado", Toast.LENGTH_SHORT).show()
        }
    }

    private fun setupBackNavigation() {
        onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                if (binding.drawerLayout.isDrawerOpen(GravityCompat.START)) {
                    binding.drawerLayout.closeDrawer(GravityCompat.START)
                } else {
                    isEnabled = false
                    onBackPressedDispatcher.onBackPressed()
                }
            }
        })
    }

    private fun replaceFragment(fragment: Fragment) {
        supportFragmentManager.beginTransaction()
            .setCustomAnimations(android.R.anim.fade_in, android.R.anim.fade_out)
            .replace(R.id.nav_host_fragment, fragment)
            .commit()
    }
}