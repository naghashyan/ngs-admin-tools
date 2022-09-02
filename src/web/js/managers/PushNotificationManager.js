let PushNotificationManager = {

  /**
   * returns true if push notifications supported
   *
   * @returns {boolean}
   */
  hasSupport: function() {
    let hasPushNotificationSupport = document.getElementById('hasPushNotificationSupport');
    return !!hasPushNotificationSupport;
  },


  /**
   * TODO: this manager is not correct, should be abstract manager,
   * init push notification listening
   */
  init: function () {
    //TODO: PubNub should not be hardcoded should be vay to pass pusher that needed
    this.transport = new PubNub({
      publishKey : document.getElementById('pushNotificationPublishKey').value,
      subscribeKey : document.getElementById('pushNotificationSubscribeKey').value,
      uuid: document.getElementById('pushNotificationUuid').value
    });

    this.subscribe();
  },


  /**
   * subscribe to event
   */
  subscribe: function() {
    let channels = document.getElementById('currentUserChannels');
    if(!channels) {
      return false;
    }

    this.transport.addListener({
      status: function(statusEvent) {
        if (statusEvent.category === "PNConnectedCategory") {
          //connected
        }
      },

      message: function(msg) {
        const pushNotificationReceived = new CustomEvent('push-notification-received', {detail: msg});
        document.dispatchEvent(pushNotificationReceived);
      },

      presence: function(presenceEvent) {
      }
    })

    channels = channels.value.split(",");
    this.transport.subscribe({
      channels: channels
    });
  },


  /**
   * subscribe to push events
   *
   * @param events
   * @param callback
   */
  subscribeToSpecificEvents: function(events, callback) {
    document.addEventListener('push-notification-received', (evt) => {
      let receivedEvent = evt.detail.message.event;
      if(events.indexOf(receivedEvent) !== -1) {
        callback(evt.detail.message);
      }
    });
  }
};
export default PushNotificationManager;