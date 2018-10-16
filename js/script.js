$(document).ready(function () {
    $('.parallax').parallax();

    $('.dropdown-trigger').dropdown({
        coverTrigger: false,
        hover: true,
        outDuration: 150
    });

    $('select').formSelect();
});
