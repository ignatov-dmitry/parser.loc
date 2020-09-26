/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import $ from 'jquery';
window.$ = window.jQuery = $;
let categories;
let count = 0;
let subCategories;
let stop = false;
let ajax = new XMLHttpRequest();

function getPlatforms(){
    $.ajax({
        'url': '/settings/load_categories_av_by',
        beforeSend: function(){
            $('#result').removeClass('alert alert-danger').html('').addClass('alert alert-warning').css('display', 'block').append('<p>Идет загрузка данных...</p>');
        },
        success: function (data) {
            let html = '';
            categories = data['categories'];
            $('#result').html('');
            categories.forEach(function (current_item) {
                count++;
                html += '<p>Название категории: '+ current_item['name'] +' url: '+ current_item['url'] +'</p>';
                current_item['models'].forEach(function (sub_category) {
                    count++;
                    html += '<p>&nbsp;&nbsp;-&nbsp;Подкатегория: '+ sub_category['name'] +' url: '+ sub_category['url'] +'</p>';
                });
            });
            $('#result').removeClass('alert-warning').addClass('alert alert-success').css('display', 'block').append('<p>Загружено категорий: '+ count +'</p>').append(html);
            $('#import_categories').removeAttr('disabled');
        },
        error: function () {
            $('#result').html('');
            $('#result').addClass('alert alert-danger').css('display', 'block').append('<p>Ошибка загрузки данных</p>');
        }
    });
}


function sendCategory(cnt = 0) {
    let plt = categories;
    let _token = $('meta[name="csrf"]').attr('content');
    let counter = cnt;
    $.ajax({
        beforeSend: function(){
            $('#import_categories').attr('disabled', 'disabled');
        },
        method: 'post',
        url: '/settings/import_category',
        data: {
            '_token': _token,
            'name': plt[counter]['name'],
            'url': plt[counter]['url'],
            'sub_categories': plt[counter]['models'] // При изменениии заменить в Category.php
        },
        success: function (parent_id) {
            cnt++;
            let progress = Math.round((100 / (plt.length / cnt)) * 100) / 100;
            $('#import_progress').text(progress + ' %').css('width', progress + '%');
            if (cnt < plt.length){
                sendCategory(cnt);
            }
            else {
                $('#result').css('display', 'none');
                getSubCategories();
            }
        }
    });
}

function getSubCategories() {
    let deferred  = $.ajax({
        method: 'get',
        url: '/settings/get_categories',
        success: function(data){
            subCategories = data;
            if (subCategories.length === 0){
                $('#count').text('Подкатегорий не найдено');
            }
            else {
                $('#count').text('Найдено подкатегорий: ' + subCategories.length);
                getTable();
            }
        }
    });
}

function getTable() {
    let ajax = new XMLHttpRequest();
    ajax.onreadystatechange = function(){
        console.log('STATE: ' + this.readyState);
        console.log('STATUS: ' + this.status);
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById("categories").innerHTML =
                this.responseText;
        }
    };
    ajax.open('GET', '/settings/get_table', true);
    ajax.send();
}



function sendCars2(cnt = 0){
    let _token = document.querySelector('meta[name="csrf"]').getAttribute('content');
    let counter = cnt;
    let selector = '#category_' + subCategories[counter]['id'] + ' .status';
    let elem = document.querySelector(selector);


    let sendData = {
        '_token': _token,
        id: subCategories[counter]['id'],
        url: subCategories[counter]['url'],
        parent_id: subCategories[counter]['parent_id']
    };



    ajax.onreadystatechange = function(){

        if (this.readyState === 1){
            elem.className = 'text-info';
            elem.textContent = 'Загрузка';
        }

        else if (this.readyState === 4 && this.status === 200) {
            let data = JSON.parse(this.response);
            elem.textContent = 'Готово ' + data['time'] + ' сек / ' + data['count'];
            elem.className = 'text-success';
            if (cnt < subCategories.length && !stop){
                cnt++;
                sendCars2(cnt);
            }
        }
    };

    ajax.open('POST', '/settings/import_cars', true);
    ajax.setRequestHeader('Content-type', 'application/json; charset=utf-8');
    ajax.send(JSON.stringify(sendData));
}



function sendCars(cnt = 0) {
    let _token = $('meta[name="csrf"]').attr('content');
    let counter = cnt;
    let selector = '#category_' + subCategories[counter]['id'] + ' .status';
    $.ajax({
        method: 'post',
        url: '/settings/import_cars',
        data: {
            '_token': _token,
            id: subCategories[counter]['id'],
            url: subCategories[counter]['url'],
            parent_id: subCategories[counter]['parent_id']
        },
        beforeSend: function(){
            $(selector).text('Загрузка').addClass('text-info');
        },
        success: function (data) {
            $(selector).text('Готово ' + data['time'] + ' сек / ' + data['count']).addClass('text-success').removeClass('text-info');
            if (cnt < subCategories.length && !stop){
                cnt++;
                sendCars(cnt);
            }
        },
        error: function () {
            $(selector).text('Ошибка').addClass('text-danger').removeClass('text-info');
            if (cnt < subCategories.length){
                cnt++;
                sendCars(cnt);
            }
        }
    });
}




getSubCategories();
function importCategories(data){
    $('#import_categories').attr('disabled', 'disabled');
    sendCategory();
}

$('#load_categories').click(function () {
    getPlatforms();
});

$('#import_categories').click(function () {
    importCategories(categories);
});

$('#import_vehicles').click(function () {
    sendCars();
});

$('#categories #import_stop').click(function () {
    stop = true;
});

$('#categories').on('click', '.category_update', function () {
    console.log(1);
});
$('#categories').on('click', '.category_delete', function () {
    console.log(2);
});

