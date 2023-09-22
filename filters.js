jQuery(document).ready(function ($) {

    $('.hide-filters-button').click(function () {
      closeFilters();
    });

function openFilters() {
$('.mobile-filter-btn').addClass('active');
$('.archive-product-page__sidebar').addClass('active');
$('.archive-product-page__content').addClass('active');
if ($(window).width() <= 568) {
  $('body').css('overflow', 'hidden');
}
}

function closeFilters() {
$('.mobile-filter-btn').removeClass('active');
$('.archive-product-page__sidebar').removeClass('active');
$('.archive-product-page__content').removeClass('active');
$('body').css('overflow', 'auto');
}

$('.mobile-filter-btn').click(function() {
if ($('.archive-product-page__sidebar').hasClass('active')) {
  closeFilters();
  $('.archive-product-page__sidebar').removeClass('sticky');
} else {
  openFilters();
  $('.archive-product-page__sidebar').addClass('sticky');
}
});


    $('.ik-filter-row-head').click(function () {
      $(this).parent().toggleClass('active');
    });
  

  $('.ik-filter-row-save').click(function () {
      $('#ik-filter-form').submit();
  });

  function labels_b(id) {
      const w1 = $('label[for=' + id + ']').width();
      const w2 = $('label[for=' + id + '] span:first-child').width();
      console.log(w1 + w2)
      $('label[for=' + id + '] span:last-child').css('width', w1 - w2 - 10)

      let p = $('.ik-filter-checkbox-list#' + id);
      if (id === 'ik-filter-order') {
          p.parent().find('input').each(function () {
              if ($(this).is(':checked')) {
                  let arr = $(this).parent().find('.label').html();
                  let val = $(this).val().split(':');
                  $('label[for=' + id + '] span:last-child').html(' : ' + arr);
                  $('#ik_f-order').val(val[0]);
                  $('#ik_f-order-type').val(val[1]);
              }
          })
      } else {
          let arr = '';
          p.parent().find('input').each(function () {
              if ($(this).is(':checked')) {
                  if (arr.length > 0) {
                      arr += ',' + $(this).parent().children('.label').html();
                  } else {
                      arr += $(this).parent().children('.label').html();
                  }
              }
          });
          $('label[for=' + id + '] span:last-child').html(' : ' + arr)
      }
  }

  $('.ik-filter-checkbox-list').each(function () {
      labels_b($(this).attr('id'));
  })

  $('.ik-filter-checkbox-item > *').click(function (e) {
      let p = $(this).parent();
      const id = p.data('id');
      labels_b(id);
  });
  $('.ik-filter-show-btn').click(function () {
      $('.products-filter').addClass('active');
  });
  $('.products-filter__close,.ik-filter-block .ik-filter-header .arr').click(function () {
      $('.products-filter').removeClass('active');
  });

  $('.ik-filter-slider-block .ik-filter-slider').each(function () {
      var obj = $(this);
      $("#" + obj.data('id') + '-slider').slider({
          range: true,
          min: obj.data('min'),
          max: obj.data('max'),
          values: [obj.data('current-min'), obj.data('current-max')],
          slide: function (event, ui) {
              $('#' + obj.data('id') + '-min').val(ui.values[0]);
              $('#' + obj.data('id') + '-max').val(ui.values[1]);
              //$("#amount").val("$" + ui.values[0] + " - $" + ui.values[1]);
              var x = $(this).offset().top - $('.ik-filter-block').offset().top;
              $('.ik-filtet-show-btn').css('top', (x - 4));
              $('.ik-filtet-show-btn').addClass('active');
          }
      });
      $("#" + obj.data('id') + '-min').change(function () {
          var new_value = parseInt($(this).val());
          var min_value = parseInt($("#" + obj.data('id') + '-slider').slider("option", "min"));
          var max_value = parseInt($("#" + obj.data('id') + '-slider').slider("option", "max"));
          if (new_value > (min_value - 1) && new_value < (max_value + 1)) {
              $("#" + obj.data('id') + '-slider').slider("values", 0, new_value);
              var x = $("#" + obj.data('id') + '-slider').offset().top - $('.ik-filter-block').offset().top;
              $('.ik-filtet-show-btn').css('top', (x - 4));
              $('.ik-filtet-show-btn').addClass('active');
          } else {
              $(this).val(min_value);
          }
      });
      $("#" + obj.data('id') + '-max').change(function () {
          var new_value = parseInt($(this).val());
          var min_value = parseInt($("#" + obj.data('id') + '-slider').slider("option", "min"));
          var max_value = parseInt($("#" + obj.data('id') + '-slider').slider("option", "max"));
          if (new_value > (min_value - 1) && new_value < (max_value + 1)) {
              $("#" + obj.data('id') + '-slider').slider("values", 1, new_value);
              var x = $("#" + obj.data('id') + '-slider').offset().top - $('.ik-filter-block').offset().top;
              $('.ik-filtet-show-btn').css('top', (x - 4));
              $('.ik-filtet-show-btn').addClass('active');
          } else {
              $(this).val(max_value);
          }
      });
  });

  $('#ik-filter-order-select').change(function () {
      setTimeout(function () {
          var order = $('#ik-filter-order-select option:selected').val();
          var order_type = $('#ik-filter-order-select option:selected').data('order');

          $('#ik_f-order').val(order);
          $('#ik_f-order-type').val(order_type);
          $('#ik-filter-form').submit();

      }, 250);
  });
  $('.ik-filtet-show-btn').click(function () {
      $('#ik-filter-form').submit();
  });
  $(".ik-filter-block").hover(
      function () {

      }, function () {
          $('.ik-filtet-show-btn').removeClass('active');
      }
  );
  $('.wc-mobile-filter-cat-btn').click(function () {
      if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          $('.wc-mobile-terms').removeClass('active');

      } else {
          $(this).addClass('active');
          $('.wc-mobile-terms').addClass('active');
      }
  });
  $('.wc-mobile-filter-select-btn').click(function () {
      if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          $('.ik-filter-block').removeClass('active');
          $("body").css("overflow", "auto");
      } else {
          $(this).addClass('active');
          $('.ik-filter-block').addClass('active');
          $("body").css("overflow", "hidden");

      }
  });
  $('.ik-filter-header, .ik-filter-close').click(function () {
      $('.wc-mobile-filter-select-btn').removeClass('active');
      $('.ik-filter-block').removeClass('active');
      $("body").css("overflow", "auto");

  });
  $('label[for=ik-filter-mobile-order-select]').click(function (e) {
      e.preventDefault();
      $(this).parent().parent().toggleClass('active');
      return false;
  });
  $('#ik-filter-mobile-order-select').change(function () {
      setTimeout(function () {
          var order = $('#ik-filter-mobile-order-select option:selected').val();
          var order_type = $('#ik-filter-mobile-order-select option:selected').data('order');

          $('#ik_f-order').val(order);
          $('#ik_f-order-type').val(order_type);

      }, 250);
  });




  


  
//Обновляем значение количества товаров при изменении фильтров	
  $('.ik-filter-row-save').on('click', function() {
  show_product_count();		
  });
  
});