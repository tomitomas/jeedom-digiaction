$('.digiaction').off('click', '.digiActionMode').on('click', '.digiActionMode', function (event) {

  eqId = $(this).closest('.eqLogic.digiaction').attr('data-eqlogic_id');

  var cmdId = $(this).attr('digi-cmdId');
  var timer = $(this).attr('digi-timer');
  if ($(this).hasClass('digiactionEnterPin')) {
    showDigit(eqId, cmdId, timer);
  }
  else {
    digiTimer(timer, eqId, null, cmdId);
  }

})

/**
** FUNCTION ON DIGICODE KEYBOARD
**/

function showDigit(_eqId, _cmdId, _timer) {
  makePanelKeyboard($('.digiaction[data-eqlogic_id=' + _eqId + '] .digiactionPanelKeyboard'));
  showOnly($('.digiaction[data-eqlogic_id=' + _eqId + ']'), '.digiactionPanelKeyboard');

  $('.digiaction[data-eqlogic_id=' + _eqId + ']').find('.digiFunctionValidate').attr('digi-cmdId', _cmdId)
  $('.digiaction[data-eqlogic_id=' + _eqId + ']').find('.digiFunctionValidate').attr('digi-timer', _timer)

}

function getEqLogicId(el) {
  return el.closest('.eqLogic.digiaction').attr('data-eqlogic_id');
}

// click on Validate button
$('.digiaction').off('click', '.digiFunctionValidate').on('click', '.digiFunctionValidate', function () {
  eqId = getEqLogicId($(this));
  cmdName = $(this).attr('digi-action');
  cmdId = $(this).attr('digi-cmdId');
  timer = $(this).attr('digi-timer');

  passwordCode = $(this).closest('.digiactionPanelKeyboard').find('.digiFilled').map(function () {
    return $(this).attr("data-l1key");
  }).get().join('');

  $(this).closest('.digiactionPanelKeyboard').find('.digiActionKeyPressed').empty()
  digiTimer(timer, eqId, passwordCode, cmdId);
})


// click on Cancel button
$('.digiaction').off('click', '.digiFunctionCancel').on('click', '.digiFunctionCancel', function () {
  showOnly($(this), '.digiactionPanelMode');
  $(this).closest('.digiactionPanelKeyboard').find('.digiActionKeyPressed').empty()
})

// click on digit : create the password code
$('.digiaction').off('click', '.digiKeyboard').on('click', '.digiKeyboard', function () {

  keyPress = $(this).text();
  li = '<li class="digiFilled userCodeAttr" data-l1key="' + keyPress + '"></li>';
  $(this).closest('.digiactionPanelKeyboard').find('.digiActionKeyPressed').append(li);
})

// remove input if RAZ is pressed
$('.digiaction').off('click', '.digiReset ').on('click', '.digiReset ', function () {
  $(this).closest('.digiactionPanelKeyboard').find('.digiActionKeyPressed').empty()
})

// stop timer and cancel action
$('.digiaction').off('click', '.digiFunctionTimerCancel').on('click', '.digiFunctionTimerCancel', function () {
  clearInterval(window.interval);
  $(this).closest('.digiactionPanelTimer').find('.digiActionCountDownTimer').empty()
  showOnly($(this), '.digiactionPanelMode');
})

// hide all panel and show only the required one
function showOnly(el, classDisplay) {
  el.closest('.eqLogic.digiaction').find('.digiactionMainPanel').hide();
  el.closest('.eqLogic.digiaction').find(classDisplay).show();
}


// ajax method : retrieve the mode available from the current mode. 
// return a html element with the new mode to display
function getAvailableMode(_eqId) {

  showOnly($('.digiaction[data-eqlogic_id=' + _eqId + ']'), '.digiactionPanelMode');

  $.ajax({
    type: "POST",
    url: "plugins/digiaction/core/ajax/digiaction.ajax.php",
    data: {
      action: "getAvailableMode",
      eqId: _eqId,
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({
          message: data.result,
          level: 'danger'
        });
        return;
      }
      var res = jQuery.parseJSON(data.result);
      $('.digiaction[data-eqlogic_id=' + _eqId + ']').find('.digiactionDisplay').html(res);

    }
  });
}

// ajax method to check the password input
// if OK then apply the new mode (run actions of the new mode)
// if not stay on the current mode
function verifUserAndDoAction(_eqId, _userCode, _cmdId) {
  $.ajax({
    type: "POST",
    url: "plugins/digiaction/core/ajax/digiaction.ajax.php",
    data: {
      action: "verifUserAndDoAction",
      eqId: _eqId,
      userCode: _userCode,
      cmdId: _cmdId,
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({
          message: data.result,
          level: 'danger'
        });
      }
      else {
        if (data.result == '') {
          getAvailableMode(_eqId);
          $('.digiaction[data-eqlogic_id=' + _eqId + ']').find('.digiactionPanelTimer').hide();
          $('.digiaction[data-eqlogic_id=' + _eqId + ']').find('.digiactionPanelKeyboard').hide();
          $('.digiaction[data-eqlogic_id=' + _eqId + ']').find('.digiactionPanelMode').show();
        }
      }
    }
  });
}


// manage the timer (if any) : display a countdown 
// which allows the user to cancel his action
function digiTimer(_timer, _eqId, _userCode, _cmdId) {

  if (_timer > 0) {
    $.ajax({
      type: "POST",
      url: "plugins/digiaction/core/ajax/digiaction.ajax.php",
      data: {
        action: "verifUser",
        eqId: _eqId,
        userCode: _userCode,
        cmdId: _cmdId,
      },
      dataType: 'json',
      error: function (request, status, error) {
        handleAjaxError(request, status, error);
        return false;
      },
      success: function (data) {
        if (data.state != 'ok') {
          $('#div_alert').showAlert({
            message: data.result,
            level: 'danger'
          });
        }
        else {
          if (data.result == 'ok') {
            var timeleft = (parseInt(_timer) + 1) * 1000;

            showOnly($('.digiaction[data-eqlogic_id=' + _eqId + ']'), '.digiactionPanelTimer');

            countDown(
              timeleft, // milliseconds
              function (restant) { // called every step to update the visible countdown
                $('.digiaction[data-eqlogic_id=' + _eqId + ']').find('.digiActionCountDownTimer').html(restant);
              },
              function () { // what to do after
                verifUserAndDoAction(_eqId, _userCode, _cmdId);
              }
            );
          }
        }
      }
    });
  }
  else {
    verifUserAndDoAction(_eqId, _userCode, _cmdId);
  }
}

// countdown function
function countDown(time, update, complete) {
  var start = new Date().getTime();
  window.interval = setInterval(function () {
    var now = time - (new Date().getTime() - start);
    if (now <= 0) {
      clearInterval(window.interval);
      complete();
    }
    else update(Math.floor(now / 1000));
  }, 100); // the smaller this number, the more accurate the timer will be
}

function shuffle(_arr) {
  for (let ii = _arr.length - 1; ii > 0; ii--) {
    let jj = Math.floor(Math.random() * (ii + 1));
    [_arr[ii], _arr[jj]] = [_arr[jj], _arr[ii]];
  }
  return _arr;
}

function makePanelKeyboard(_digiPanelKeyboard) {
  let html = '';
  let arr = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
  if (_digiPanelKeyboard.attr('data-randomkeys') == 1) {
    arr = shuffle(arr);
  }
  for (let ii = 0; ii < 3; ii++) {
    html += '<div>';
    for (let jj = 0; jj < 3; jj++) {
      html += '<li class="digiKeyboard">'+arr[ii*3+jj]+'</li>';
    }
    html += '</div>';
  }
  html += '<div>';
  html += '<li class="digiKeyboard">A</li>';
  html += '<li class="digiKeyboard">'+arr[arr.length - 1]+'</li>';
  html += '<li class="digiKeyboard">B</li>';
  html += '</div>';
  html += '<div>';
  html += '<li class="digiFunction digiFunctionValidate digiActionBgGreen">V</li>';
  html += '<li class="digiReset digiActionBgYellow">RAZ</li>';
  html += '<li class="digiFunction digiFunctionCancel digiActionBgRed">A</li>';
  html += '</div>';
                
  _digiPanelKeyboard.find('.digiactionPanel').html(html);
}

if (typeof jeedom.cmd.addUpdateFunction !== 'function') {
  jeedom.cmd.addUpdateFunction = function (id, func) {
    jeedom.cmd.update[id] = func;
  }
}
