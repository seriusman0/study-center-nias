package id.scnias.app

import android.app.Application
import id.scnias.app.core.AppGraph

class ScNiasApp : Application() {
    override fun onCreate() {
        super.onCreate()
        AppGraph.init(this)
    }
}
