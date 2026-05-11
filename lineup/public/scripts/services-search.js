var isAdmin = document.getElementById('services-container').dataset.admin === '1';
var imgUrl = 'https://images.unsplash.com/photo-1621605815971-fbc98d665033?q=80&w=2670&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D';

(function () {
  var input = document.getElementById('service-search');
  var container = document.getElementById('services-container');
  var timer = null;

  function buildCard(service) {
    var buttons = isAdmin
      ? '<a href="/admin/services" class="btn btn-sm btn-outline-secondary">Edit</a>' +
        '<form action="/admin/services/delete/' + service.id + '" method=post>' +
        '<button type="submit" class="btn btn-sm btn-outline-secondary">Delete</button></form>'
      : '<a href="/book?service_id=' + service.id + '" class="btn btn-sm btn-outline-secondary">Book</a>';

    return '<div class="col">' +
      '<div class="card shadow-sm">' +
      '<img src="' + imgUrl + '" class="card-img-top" alt="' + service.name + '">' +
      '<div class="card-body">' +
      '<h5 class="card-title">' + service.name + '</h5>' +
      '<p class="card-text">' + service.description + '</p>' +
      '<div class="d-flex justify-content-between align-items-center">' +
      '<div class="btn-group">' + buttons + '</div>' +
      '<small class="text-muted">$' + service.price + '</small>' +
      '</div></div></div></div>';
  }

  function render(services) {
    if (services.length === 0) {
      container.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted fs-5">No services found.</p></div>';
      return;
    }
    container.innerHTML = services.map(buildCard).join('');
  }

  input.addEventListener('input', function () {
    clearTimeout(timer);
    var q = input.value.trim();
    timer = setTimeout(function () {
      fetch('/api/services/search?q=' + encodeURIComponent(q))
        .then(function (r) { return r.json(); })
        .then(function (data) { render(data); })
        .catch(function () {});
    }, 300);
  });
})();
