function openNav() {
    var sidebar = document.getElementById("sidebar");
    const container = document.querySelector(".container");

    if (sidebar.classList.contains("open")) {
        sidebar.classList.remove("open");
        container.classList.remove("shifted");
    } else {
        sidebar.classList.add("open");
        container.classList.add("shifted");
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var h1 = document.querySelector('#intro > h1');
    var h2 = document.querySelector('#intro > button');
    if (!h1) return;
    var observer = new window.IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                h1.classList.add('slide-in');
                h2.classList.add('slide-in');
            } else {
                h1.classList.remove('slide-in');
                h2.classList.remove('slide-in');
            }
        });
    }, { threshold: 0.1 });
    observer.observe(h1);
});

document.addEventListener('DOMContentLoaded', function() {
    var intro = document.getElementById('intro');
    var logo = document.querySelector('.logo');
    var observer = new window.IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            logo.classList.add('logo-hidden');
            logo.classLis.remove('logo-shown');
        } else {
            logo.classList.add('logo-shown');
            logo.classList.remove('logo-hidden');
        }
        });
    }, { threshold: 0.1 });
    observer.observe(intro);
    });

