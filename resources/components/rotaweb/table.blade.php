@php
  // Aceita $rows como array de itens (array|object). Se vier 1 item associativo, normaliza.
  $rows = $rows ?? [];
  $rows = is_array($rows) ? $rows : (array) $rows;
  $isAssocTop = $rows && array_keys($rows) !== range(0, count($rows)-1);
  if ($isAssocTop) { $rows = [$rows]; }

  // Se vazio:
  if (!$rows || count($rows) === 0) {
    echo '<div class="alert alert-warning mb-0">Nenhum dado retornado.</div>';
    return;
  }

  // Descobrir colunas a partir do primeiro item
  $first = is_array($rows[0]) ? $rows[0] : (array) $rows[0];
  $cols  = array_keys($first);
@endphp

<div class="table-responsive">
  <table id="{{ $tableId ?? 'rotaweb-table' }}" class="table table-striped table-bordered table-sm align-middle">
    <thead class="table-light">
      <tr>
        @foreach ($cols as $c)
          <th>{{ $c }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @foreach ($rows as $r)
        @php $r = is_array($r) ? $r : (array) $r; @endphp
        <tr>
          @foreach ($cols as $c)
            <td>
              @php
                $val = $r[$c] ?? '';
                if (is_array($val) || is_object($val)) {
                  echo e(json_encode($val, JSON_UNESCAPED_UNICODE));
                } else {
                  echo e($val);
                }
              @endphp
            </td>
          @endforeach
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

@if (!isset($hideExport) || !$hideExport)
  <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="exportTableToCSV('{{ $csvName ?? 'rotaweb-export.csv' }}', '{{ $tableId ?? 'rotaweb-table' }}')">
    <i class="bi bi-download"></i> Exportar CSV
  </button>

  @push('scripts')
  <script>
    function exportTableToCSV(filename, tableId) {
      const table = document.getElementById(tableId);
      if (!table) return;
      let csv = [];
      const rows = table.querySelectorAll('tr');
      for (let i = 0; i < rows.length; i++) {
        const cols = rows[i].querySelectorAll('th, td');
        const row = [];
        for (let j = 0; j < cols.length; j++) {
          // escapa aspas duplas
          let text = cols[j].innerText.replace(/"/g, '""');
          // envolve em aspas para preservar vírgulas
          row.push('"' + text + '"');
        }
        csv.push(row.join(','));
      }
      const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      link.setAttribute('download', filename);
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  </script>
  @endpush
@endif
