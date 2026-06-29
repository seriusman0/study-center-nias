-keep class com.squareup.moshi.** { *; }
-keep @com.squareup.moshi.JsonClass class * { *; }
-keepclassmembers class **JsonAdapter { *; }
-keep class kotlin.Metadata { *; }
-keepattributes Signature, *Annotation*

-keep class id.scnias.app.data.dto.** { *; }
