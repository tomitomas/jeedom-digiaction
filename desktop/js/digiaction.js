
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

MODE_LIST = null;
RENAME_LIST = [];

$(window).on('load', function() {
  var style = $('<style>.img-responsive { display: inline!important; } /*input[type=checkbox]{ outline: 1px solid #b42828 !important;}*/</style>');
  $('html > head').append(style);
 });


$('#bt_addMode').off('click').on('click', function () {
  
  bootbox.prompt("{{Nom du mode ?}}", function (result) {
    if (result !== null && result != '') {
      newName = $.trim(result) ; 
      if (MODE_LIST != null && $.inArray( newName, MODE_LIST) > -1 ){ 
        $('#div_alert').showAlert({message: 'Désolé ce nom existe déjà', level: 'danger'});
        return; 
      }
      addMode({name: newName});
    }
  });
});

$('body').off('click','.rename').on('click','.rename',  function () {
  var el = $(this);
  bootbox.prompt("{{Nouveau nom ?}}", function (result) {
    if (result !== null && result != '') {
      newName = $.trim(result) ; 
      if (MODE_LIST != null && $.inArray( newName, MODE_LIST) > -1 ){ 
        $('#div_alert').showAlert({message: 'Désolé ce nom existe déjà', level: 'danger'});
        return; 
      }
      var previousName = el.text();
      el.text(newName);
      el.closest('.panel.panel-default').find('span.name').text(newName);
      renameCheckboxMode(previousName ,  newName );
      
      var rename = {};
      rename['old'] = previousName;
      rename['new'] = newName;
      RENAME_LIST.push(rename);
      
    }
  });
});


$("body").off('click','.listCmdInfo').on( 'click','.listCmdInfo', function () {
    var type = $(this).attr('data-type');
    var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmdInfo]');
    jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
      el.value(result.human);
      jeedom.cmd.displayActionOption(el.value(), '', function (html) {
        el.closest('.' + type).find('.actionOptions').html(html);
        taAutosize();
      });
    });
});


$("body").off('click','.listCmdAction').on( 'click','.listCmdAction', function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
      taAutosize();
    });
  });
});

$("body").off('click','.listAction').on( 'click','.listAction',function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  jeedom.getSelectActionModal({}, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
      taAutosize();
    });
  });
});

$("body").off('click', '.bt_removeAction').on( 'click', '.bt_removeAction',function () {
  var type = $(this).attr('data-type');
  $(this).closest('.' + type).remove();
});

$("#div_modes").off('click','.bt_addPreCheck').on('click','.bt_addPreCheck',  function () {
  addAction({}, 'preCheck', '{{Pré-check}}', $(this).closest('.mode'));
});

$("#div_modes").off('click','.bt_addPreCheckActionError').on( 'click','.bt_addPreCheckActionError',function () {
  addAction({}, 'preCheckActionError', '{{Pré-check Erreur}}', $(this).closest('.mode'));
});

$("#div_modes").off('click','.bt_addDoAction').on( 'click','.bt_addDoAction',function () {
  addAction({}, 'doAction', '{{Action}}', $(this).closest('.mode'));
});

$('body').off('focusout','.cmdAction.expressionAttr[data-l1key=cmd]').on( 'focusout', '.cmdAction.expressionAttr[data-l1key=cmd]',function (event) {
  var type = $(this).attr('data-type')
  var expression = $(this).closest('.' + type).getValues('.expressionAttr');
  var el = $(this);
  jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
    el.closest('.' + type).find('.actionOptions').html(html);
    taAutosize();
  })
});

$("#div_modes").off('click','.bt_removeMode').on('click', '.bt_removeMode',function () {
  $(this).closest('.mode').remove();
  updateCheckboxMode();
});

$('body').off('click','.mode .modeAction[data-l1key=chooseIcon]').on('click','.mode .modeAction[data-l1key=chooseIcon]',  function () {
  var mode = $(this).closest('.mode');
  chooseIcon(function (_icon) {
    mode.find('.modeAttr[data-l1key=icon]').empty().append(_icon);
  }, {img : true});
});

$('body').off('click','.mode .modeAttr[data-l1key=icon]').on( 'click','.mode .modeAttr[data-l1key=icon]', function () {
  $(this).empty();
});

$('.nav-tabs li a').off('click').on('click',function(){
  setTimeout(function(){
    taAutosize();
  }, 50);
})

$('#div_modes').off('click','.panel-heading').on('click','.panel-heading',function(){
  setTimeout(function(){
    taAutosize();
  }, 50);
})

$("#div_modes").sortable({axis: "y", cursor: "move", items: ".mode", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

function printEqLogic(_eqLogic) {
  $('#div_modes').empty();
  MODE_LIST = [];
  if (isset(_eqLogic.configuration) && isset(_eqLogic.configuration.modes)) {
    actionOptions = []
    for (var i in _eqLogic.configuration.modes) {
      MODE_LIST.push(_eqLogic.configuration.modes[i].name)
    }
    for (var i in _eqLogic.configuration.modes) {
      addMode(_eqLogic.configuration.modes[i],false);
    }
    MODE_LIST = null
    jeedom.cmd.displayActionsOption({
      params : actionOptions,
      async : false,
      error: function (error) {
        $('#div_alert').showAlert({message: error.message, level: 'danger'});
      },
      success : function(data){
        for(var i in data){
          $('#'+data[i].id).append(data[i].html.html);
        }
        taAutosize();
      }
    });
  }
  if (isset(_eqLogic.configuration) && isset(_eqLogic.configuration.users)) {
    $('#table_user .user').remove();
    for (var i in _eqLogic.configuration.users) {
      addUserToTable(_eqLogic.configuration.users[i]);
    }
  }
}

function saveEqLogic(_eqLogic) {
  if (!isset(_eqLogic.configuration)) {
    _eqLogic.configuration = {};
  }
  _eqLogic.configuration.modes = [];
  $('#div_modes .mode').each(function () {
    var mode = $(this).getValues('.modeAttr')[0];
    mode.preCheck = $(this).find('.preCheck').getValues('.expressionAttr');
    mode.preCheckActionError = $(this).find('.preCheckActionError').getValues('.expressionAttr');
    mode.doAction = $(this).find('.doAction').getValues('.expressionAttr');
    mode.availableMode = $(this).find('.modeAvailable').getValues('.expressionAttr') ;
    _eqLogic.configuration.modes.push(mode);
  });
  _eqLogic.configuration.users = [];
  $('#usertab .user').each(function () {
    var user = $(this).getValues('.userAttr');
    _eqLogic.configuration.users.push(user[0]);
  });

  if ( RENAME_LIST.length > 0){
    renameCmdConfig(_eqLogic.id, RENAME_LIST ); 
  }

  return _eqLogic;
}

function addMode(_mode,_updateMode) {
  if (init(_mode.name) == '') {
    return;
  }
  var random = Math.floor((Math.random() * 1000000) + 1);
  var div = '<div class="mode panel panel-default">';
  div += '<div class="panel-heading">';
  div += '<h3 class="panel-title">';
  div += '<a class="accordion-toggle" data-toggle="collapse" data-parent="#div_modes" href="#collapse' + random + '">';
  div += '<span class="name">' + _mode.name + '</span>';
  div += '</a>';
  div += '</h3>';
  div += '</div>';
  
  div += '<div id="collapse' + random + '" class="panel-collapse collapse in">';
  div += '<div class="panel-body">';
  div += '<div class="well">';
  
  div += '<form class="form-horizontal" role="form">';
  
  div += '<div class="col-lg-3 col-sm-12 pull-right">';
  div += '<div class="input-group pull-right" style="display:inline-flex">';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-sm bt_removeMode btn-danger roundedLeft"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>';
  div += '<a class="btn btn-sm bt_addPreCheck btn-warning"><i class="fas fa-plus-circle"></i> {{Pré-check}}</a>';
  div += '<a class="btn btn-sm bt_addPreCheckActionError btn-default" title="Réaliser une action si les pré-check échouent"><i class="fas fa-plus-circle"></i> {{Pré-check Erreur}}</a>';
  div += '<a class="btn btn-sm bt_addDoAction btn-success"><i class="fas fa-plus-circle"></i> {{Action}}</a>';
  div += '</span>';
  div += '</div>';
  div += '</div>';
  
  div += '<div class="form-group">';
  div += '<div class="col-lg-2 col-sm-6">';
  div += '<label class="control-label" style="margin-right:7px">{{Nom}}</label>';
  div += '<span class="modeAttr label rename cursor" style="display:inline" data-l1key="name"></span>';
  div += '</div>';
  
  div += '<div class="col-lg-3 col-sm-6 digiSetupIcone">';
  div += '<label class="control-label" style="margin-right:7px">{{Icône}}</label>';
  div += '<a class="modeAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fas fa-flag"></i> {{Icône}}</a>';
  div += ' <span class="modeAttr label cursor" data-l1key="icon" style="display:inline"></span>';
  div += '</div>';
  
  div += '<div class="col-lg-2 col-sm-6">';
  div += '<label class="col-sm-6 control-label" style="margin-right:7px" title="{{avant activation du mode}}">{{Délais (en sec)}}</label>';
  div += '<div class="col-sm-4">';
  div += '<input type="number" class="modeAttr" min="0" data-l1key="timer" placeholder="{{délais}}" title="{{avant activation du mode}}" style="width:70px;" value="0" />';
  div += '</div>';
  div += '</div>';

  div += '<div class="col-lg-2 col-sm-6">';
  div += '<label class="checkbox-inline">';
  div += '<label class="checkbox-inline">';
  div += '<input type="checkbox" class="modeAttr" data-l1key="confirmDigicode">{{Confirmation par mot de passe}}';
  div += '</label>';
  div += '</div>';
  
  div += '</div>';

  div += '<hr/>';
  div += '<div class="div_preCheck"></div>';
  div += '<hr/>';
  div += '<div class="div_preCheckActionError"></div>';
  div += '<hr/>';
  div += '<div class="div_doAction"></div>';
  //-------
  div += '<hr/>';
  div += '<div class="div">';
  div += '<label class="control-label" style="margin-right:7px">{{Mode Dispo}}</label>';
  div += '<div class="modeAvailable">';
  div += '</div>';
  div += '</div>';
  //------
  div += '</form>';
  div += '</div>';
  div += '</div>';
  div += '</div>';
  div += '</div>';
  
  $('#div_modes').append(div);
  $('#div_modes .mode').last().setValues(_mode, '.modeAttr');
  if (is_array(_mode.preCheck)) {
    for (var i in _mode.preCheck) {
      addAction(_mode.preCheck[i], 'preCheck', '{{Pré-check}}', $('#div_modes .mode').last());
    }
  } else {
    if ($.trim(_mode.preCheck) != '') {
      addAction(_mode.preCheck[i], 'preCheck', '{{Pré-check}}', $('#div_modes .mode').last());
    }
  }

  if (is_array(_mode.preCheckActionError)) {
    for (var i in _mode.preCheckActionError) {
      addAction(_mode.preCheckActionError[i], 'preCheckActionError', '{{Pré-check Erreur}}', $('#div_modes .mode').last());
    }
  } else {
    if ($.trim(_mode.preCheckActionError) != '') {
      addAction(_mode.preCheckActionError[i], 'preCheckActionError', '{{Pré-check Erreur}}', $('#div_modes .mode').last());
    }
  }
  
  if (is_array(_mode.doAction)) {
    for (var i in _mode.doAction) {
      addAction(_mode.doAction[i], 'doAction', '{{Action}}', $('#div_modes .mode').last());
    }
  } else {
    if ($.trim(_mode.doAction) != '') {
      addAction(_mode.doAction, 'doAction', '{{Action}}', $('#div_modes .mode').last());
    }
  }
  $('.collapse').collapse();
  $("#div_modes .mode:last .div_preCheck").sortable({axis: "y", cursor: "move", items: ".preCheck", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
  $("#div_modes .mode:last .div_preCheckActionError").sortable({axis: "y", cursor: "move", items: ".preCheckActionError", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
  $("#div_modes .mode:last .div_doAction").sortable({axis: "y", cursor: "move", items: ".doAction", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
  if ( isset(_mode.availableMode) && _mode.availableMode[0] !== {} ) {
    var checkbox = addCheckboxMode(_mode.availableMode);
    $('#div_modes .mode:last .modeAvailable').append(checkbox);
  } else {
    updateCheckboxMode();
  }

}

function addAction(_action, _type, _name, _el) {
  if (!isset(_action)) {
    _action = {};
  }
  if (!isset(_action.options)) {
    _action.options = {};
  }
  var input = '';
  var button = 'btn-default';
  if (_type == 'doAction') {
    input = 'has-success';
    button = 'btn-success';
  }
  if (_type == 'preCheck') {
    input = 'has-warning';
    button = 'btn-warning';
  }
  if (_type == 'preCheckActionError') {
    input = 'has-default';
    button = 'btn-default';
  }
  var div = '<div class="' + _type + '">';
  div += '<div class="form-group ">';
  div += '<label class="col-sm-1 control-label">' + _name + '</label>';
  div += '<div class="col-sm-1  ' + input + '">';
  if (_type == 'doAction') {
    div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'action}}" />';
  }
  else if (_type == 'preCheckActionError') {
    div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'action}}" />';
  }
  else {
    div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver le test}}" />';
  }
  div += '</div>';
  div += '<div class="col-sm-5 ' + input + '">';
  div += '<div class="input-group">';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default bt_removeAction btn-sm" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>';
  div += '</span>';
  if (_type == 'doAction') {
    div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
    div += '<span class="input-group-btn">';
    div += '<a class="btn ' + button + ' btn-sm listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fas fa-tasks"></i></a>';
    div += '<a class="btn ' + button + ' btn-sm listCmdAction" data-type="' + _type + '" title="{{Sélectionner une commande action}}"><i class="fas fa-list-alt"></i></a>';
  }
  else if (_type == 'preCheckActionError') {
    div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
    div += '<span class="input-group-btn">';
    div += '<a class="btn ' + button + ' btn-sm listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fas fa-tasks"></i></a>';
    div += '<a class="btn ' + button + ' btn-sm listCmdAction" data-type="' + _type + '" title="{{Sélectionner une commande action}}"><i class="fas fa-list-alt"></i></a>';
  }
  else{
    div += '<input class="expressionAttr form-control input-sm cmdInfo" data-l1key="cmdInfo" data-type="' + _type + '" />';
    div += '<span class="input-group-btn">';
    div += '<a class="btn ' + button + ' btn-sm listCmdInfo" data-type="' + _type + '" title="{{Sélectionner une commande info}}"><i class="fas fa-list-alt"></i></a>';
  }
  div += '</span>';
  div += '</div>';
  div += '</div>';
  var actionOption_id = uniqId();
  div += '<div class="col-sm-5 actionOptions" id="'+actionOption_id+'">';
  div += '</div>';
  div += '</div>';
  if (isset(_el)) {
    _el.find('.div_' + _type).append(div);
    _el.find('.' + _type + '').last().setValues(_action, '.expressionAttr');
  } else {
    $('#div_' + _type).append(div);
    $('#div_' + _type + ' .' + _type + '').last().setValues(_action, '.expressionAttr');
  }
  if(actionOptions){
    actionOptions.push({
      expression : init(_action.cmd, ''),
      options : _action.options,
      id : actionOption_id
    });
    // $('.actionOptions .input-group .input-group-addon').addClass('digiActionBgGreen') ; 
  }
}

$('input[data-l1key=configuration][data-l2key=autocall]').change( function () {
  updateCheckboxMode();
});


function updateCheckboxMode(){

    var autocall= $('input[data-l1key=configuration][data-l2key=autocall]').is(':checked');
    
    $('.modeAvailable').each(function () {

      var currentName = $(this).parents('.mode').find("a span.name").html();
      
      var actualChecked = [];

      $(this).find("input[type=checkbox]").each(function() {
        if ($(this).is(":checked")) {
          actualChecked.push($(this).attr('data-l1key'));
        }
      });

      $(this).empty();
      var options = '';
      
      if(MODE_LIST != null){
        for(var i in MODE_LIST){
          if ( autocall || currentName != MODE_LIST[i] ) {
            var check = '';
            if (actualChecked.indexOf(MODE_LIST[i]) > -1){ 
              var check = 'checked';
            }
            options += '<label class="checkbox-inline">';
            options += '<input type="checkbox" class="expressionAttr" data-l1key="'+MODE_LIST[i]+'" ' + check +' /><span>'+MODE_LIST[i]+'</span>' ;
            options += '</label>';
          }
        }
      }else{
        $('#div_modes .mode').each(function () {
          tmpName = $(this).getValues('.modeAttr')[0].name ;
          if ( autocall || currentName != tmpName ) {
            var check = '';
            if (actualChecked.indexOf(tmpName) > -1){
              var check = 'checked';
            }
            options += '<label class="checkbox-inline">';
            options += '<input type="checkbox" class="expressionAttr" data-l1key="'+tmpName+'" ' + check +' /><span>'+tmpName+'</span>' ;
            options += '</label>';
          }
        });
      }
      $(this).append(options);
  });
}


function addCheckboxMode(_availableMode){
    var options = '';

    for (const [key, value] of Object.entries(_availableMode[0])) {
      check= '';
      if ( value == 1){
        check='checked' ;
      }
      options += '<label class="checkbox-inline">';
      options += '<input type="checkbox" class="expressionAttr" data-l1key="'+key+'" ' + check +' /><span>'+key+'</span>' ;
      options += '</label>';
    }

    return options;
}

function renameCheckboxMode(_previousName , _newName ){

  $('#div_modes .modeAvailable input[type=checkbox]').each(function(i){
    if ( $(this).attr('data-l1key') == _previousName){
      $(this).attr('data-l1key', _newName);
      $(this).siblings('span').text(_newName);
    }
    
  });

}

/*
 when renaming a mode it will delete the previous command and create a new one
 this function is used to update the name & logicalId of the existing cmd instead of deleting it
*/
function renameCmdConfig(_eqId, _arrayRename ){
  $.ajax({
    type: "POST",
    url: "plugins/digiaction/core/ajax/digiaction.ajax.php",
    data: {
      action: "updateCmdConfig",
      eqId: _eqId,
      arrayRename: _arrayRename
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
      return;
    }
  });
}

/*
* Permet la réorganisation des commandes dans l'équipement
*/
$("#table_user").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

/*
* Fonction permettant l'affichage des commandes dans l'équipement
*/

$('#bt_addUser').off('click').on('click', function () {
  
      addUserToTable();
    
  
});


function addUserToTable(_user) {
  if (! isset(_user)){
    _user = {};
  }
   var random = Math.floor((Math.random() * 1000000) + 1);
   var tr = '<tr class="user cmdUser">';
   tr += '<td style="min-width:300px;width:350px;">';
   tr += '<input class="userAttr form-control input-sm" data-l1key="name" placeholder="{{Nom de l\'utilisateur}}">';
   tr += '</td>';
   tr += '<td style="min-width:300px;width:350px;">';
   tr += '<input class="userAttr form-control input-sm" data-l1key="userCode" placeholder="{{Code de l\'utilisateur}}">';
   tr += '</td>';
   tr += '<td>';
   tr += '<i class="fas fa-minus-circle pull-right cursor bt_removeAction" data-type="user" data-action="remove"></i>';
   tr += '</td>';
   tr += '</tr>';
   $('#table_user tbody').append(tr);
   var tr = $('#table_user tbody tr').last();
   tr.setValues(_user, '.userAttr');
 }
