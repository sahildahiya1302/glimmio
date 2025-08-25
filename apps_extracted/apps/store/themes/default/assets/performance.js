document.addEventListener('DOMContentLoaded', function () {
  if (!window.performance || !performance.timing) return;

  function gatherSuggestions() {
    var t = performance.timing;
    var loadTime = t.domContentLoadedEventEnd - t.navigationStart;
    var suggestions = [];
    if (loadTime > 3000) {
      suggestions.push('Consider reducing critical resource size to speed up initial load.');
    }
    var resources = performance.getEntriesByType('resource');
    var heavyImages = resources.filter(function (r) {
      return r.initiatorType === 'img' && r.transferSize > 300000;
    });
    if (heavyImages.length > 0) {
      suggestions.push('Optimize ' + heavyImages.length + ' large image' + (heavyImages.length > 1 ? 's' : '') + '.');
    }
    return suggestions;
  }

  function displaySuggestions() {
    var tips = gatherSuggestions();
    if (!tips.length) return;
    var panel = document.createElement('div');
    panel.id = 'perf-suggestions';
    panel.style.cssText = 'position:fixed;bottom:10px;right:10px;background:rgba(0,0,0,0.7);color:#fff;padding:10px 15px;font-size:12px;z-index:9999;border-radius:4px;max-width:250px;';
    panel.innerHTML = '<strong>Performance Tips</strong><ul style="margin:5px 0 0;padding-left:20px;">' + tips.map(function (t) { return '<li>' + t + '</li>'; }).join('') + '</ul>';
    document.body.appendChild(panel);
  }

  setTimeout(displaySuggestions, 0);
});
