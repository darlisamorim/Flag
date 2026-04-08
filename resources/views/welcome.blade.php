<body style="font-family:monospace;padding:2rem;background:#111;color:#fff;">
<img src="{{ asset('storage/' . $user->avatar) }}" style="width:80px;border-radius:50%;">
<h1>{{ $user->name }}</h1>
<p>{{ $user->title }} — {{ $user->role }}</p>
<p>{{ $user->subname }}</p>
<p>{{ $user->bio }}</p>
<hr style="margin:1rem 0;">
<p>📧 {{ $user->email }}</p>
<p>📱 {{ $user->phone }}</p>
<p>📍 {{ $user->addr }}, {{ $user->district }}, {{ $user->location }}, {{ $user->zip }}, {{ $user->country }}</p>
<hr style="margin:1rem 0;">
<p>🌐 <a href="{{ $user->website }}" style="color:#e3000b;">{{ $user->website }}</a></p>
<p>GitHub: <a href="{{ $user->github }}" style="color:#e3000b;">{{ $user->github }}</a></p>
<p>LinkedIn: <a href="{{ $user->linkedin }}" style="color:#e3000b;">{{ $user->linkedin }}</a></p>
<p>Instagram: <a href="{{ $user->instagram }}" style="color:#e3000b;">{{ $user->instagram }}</a></p>
<p>TikTok: <a href="{{ $user->tiktok }}" style="color:#e3000b;">{{ $user->tiktok }}</a></p>
<p>YouTube: <a href="{{ $user->youtube }}" style="color:#e3000b;">{{ $user->youtube }}</a></p>
</body>
