

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="fas fa-circle-xmark fa-lg mt-1 flex-shrink-0"></i>
        <div>
            <strong>Periksa kembali form:</strong>
            <ul class="mb-0 ps-3 mt-1 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
