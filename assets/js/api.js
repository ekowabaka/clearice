document.addEventListener(
  'DOMContentLoaded',
  function(){
    var height = document.getElementById('header').offsetHeight;
    document.getElementById('side').style.height = (window.outerHeight - height) + 'px';
    document.getElementById('side').style.top = height + 'px';
    document.getElementById('namespaces-list').style.height = ((window.innerHeight - height) * .33) + 'px';
    document.getElementById('namespace-items-list').style.height = ((window.innerHeight - height) * .67) + 'px';
  }
);

