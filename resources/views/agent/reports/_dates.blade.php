<form method="GET" class="row g-3 align-items-end mb-4">
    <div class="col-md-3">
        <label for="from" class="form-label">From</label>
        <input type="date" class="form-control" id="from" name="from" value="{{ $from }}">
    </div>
    <div class="col-md-3">
        <label for="to" class="form-label">To</label>
        <input type="date" class="form-control" id="to" name="to" value="{{ $to }}">
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-primary">Show</button>
    </div>
</form>
