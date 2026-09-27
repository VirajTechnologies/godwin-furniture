@if (! $record->isActive())
    <form method="POST" action="{{ $activateUrl }}">
        @csrf
        <input type="hidden" name="status" value="active">
        <button type="submit" class="btn btn-sm btn-soft-success">Activate</button>
    </form>
@endif
