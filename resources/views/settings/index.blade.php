@extends('layouts.template')
@section('content')
    <section>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-9">

                    <button class="btn btn-success" id="load_categories" type="submit">Загрузить категории</button>
                    <button class="btn btn-success" id="import_categories" disabled=""  type="submit">Импортировать категории</button>
                    <button class="btn btn-success" id="import_vehicles" type="submit">Импортировать транспорт</button>
                    <button class="btn btn-success" id="import_stop">Стоп</button>
                    <div id="count"></div>
                    <div class="progress">
                        <div class="progress-bar" id="import_progress" role="progressbar" style="width: 0%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    <div id="result" class="alert" style="display: none" role="alert">
                        <h4 class="alert-heading">Информация</h4>
                    </div>

                    <div id="categories">

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
