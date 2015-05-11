document.addEventListener(
  'DOMContentLoaded',
  function(){
    var height = document.getElementById('header').offsetHeight;
    var side = document.getElementById('side');
    if(side)
    {
      side.style.height = (window.outerHeight - height) + 'px';
      document.getElementById('book-toc-wrapper').style.height = (window.outerHeight - height) + 'px';
      side.style.top = height + 'px';
    }
  }
);
  