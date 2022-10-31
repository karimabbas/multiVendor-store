@if (session()->has($type))
    <div class="alert alert-{{ $type }}" id=Message>
        {{ session($type) }}
    </div>
@endif

<script>
    setTimeout(function() {
        $('#Message').fadeOut('fast');
    }, 3000);
</script>
