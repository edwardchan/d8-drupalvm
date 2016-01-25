(function ($, Drupal, drupalSettings) {
  
  Drupal.behaviors.nodejs_ajax = {
    attach: function () {
      // Fake element to bind the Drupal ajax object to.
      // (The ajax behavior is normally triggered by events in this element, but, in this case, this is irrelevant
      // because the command will be triggered by a message from nodejs.)
      var element = $('');

      Drupal.nodejs_ajax = new Drupal.ajax({
        base: 'nodejs_ajax',
        element: element,
        url: ''
      });

      Drupal.nodejs_ajax.runCommands = function(message) {
        var response = message.commands;
        for (var i in response) {
          if (response[i]['command'] && Drupal.nodejs_ajax.commands[response[i]['command']]) {
            Drupal.nodejs_ajax.commands[response[i]['command']](Drupal.nodejs_ajax, response[i], 200);
          }
        }
      }
    }
  };

  Drupal.Nodejs.callbacks.nodejsNodeAjaxBroadcast = {
    callback: function (message) {
      switch (message.channel) {
        case 'nodejs_ajax_broadcast':
          Drupal.nodejs_ajax.runCommands(message);
          break;
      }
    }
  };

  Drupal.Nodejs.callbacks.nodejsNodeAjax = {
    callback: function (message) {
      Drupal.nodejs_ajax.runCommands(message);
    }
  };

})(jQuery, Drupal, drupalSettings);

