function custom(){console.log('custom script');}

function trackEvent(type, data={}) {
  fetch('/api/events.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({type: type, data: data})
  }).catch(() => {});
}
