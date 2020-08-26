<table class="table table-hover">
    <tr>
        <th></th>
        <th>Название категории</th>
        <th>url</th>
        <th></th>
        <th></th>
        <th>Статус / Количество записей</th>
    </tr>
    @foreach($categories as $category)
        @if($category['parent_id'] == 0)
            <tr class="thead-dark">
                <th colspan="6"><strong>{{ $category['name'] }}</strong></th>
            </tr>
        @else
            <tr data-category_id="{{ $category['id'] }}" id="category_{{ $category['id'] }}">
                <td></td>
                <td>{{ $category['name'] }}</td>
                <td>{{ $category['url'] }}</td>
                <td><button class="btn btn-success category_update">Обновить</button></td>
                <td><button class="btn btn-danger category_delete">Удалить</button></td>
                <td class="status"></td>
            </tr>
        @endif
    @endforeach
</table>
