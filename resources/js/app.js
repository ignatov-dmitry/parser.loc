/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});


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

