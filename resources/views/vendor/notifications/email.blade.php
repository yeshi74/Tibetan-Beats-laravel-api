@component('mail::message')
{{-- App Logo --}}
<div style="text-align:center; margin-bottom: 20px;">
    <img src="{{ asset('images/logo.png') }}" alt="Music App Logo" width="80">
</div>

# Welcome to Tibetan Music 🎵

Hello **{{ $user->name ?? 'Music Lover' }}**,

Thanks for signing up! To get started, please verify your email address by clicking the button below:

@component('mail::button', ['url' => $url, 'color' => 'success'])
Verify My Email
@endcomponent

Once verified, you’ll be able to **login and explore thousands of songs, albums, and playlists** curated just for you.  

---

🎧 **Why verify your email?**  
- Secure your account  
- Get personalized recommendations  
- Save playlists and favorites  

If you did not create an account, no further action is required.  

Thanks for joining our community,  
**Tibetan Music Team**

---

@component('mail::subcopy')
If you’re having trouble clicking the "Verify My Email" button, copy and paste this URL into your browser:  
[{{ $url }}]({{ $url }})
@endcomponent
@endcomponent
