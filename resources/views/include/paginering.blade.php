@if (isset($paginering) && !empty($paginering))
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center" id="paginering">
            @foreach ($paginering as $pagina)
                <li class="page-item {{ $pagina[2] }}">
                    <a class="page-link" href="#" data-pagina="{{ $pagina[0] }}">
                        {!! $pagina[1] !!}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
