<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <input type="email" name="email" required>
    <button type="submit">Email Password Reset Link</button>
</form>
