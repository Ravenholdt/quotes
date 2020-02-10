function start(){
	axios.post('update.php', {'action': 'start'})
		.then(function (response) {
			update(response.data, true);
        });
}

function change(a, swipe = false){
    let quote1 = $('#quote1');
    let quote2 = $('#quote2');
	axios.post('update.php', {
		'action': 'change',
		'id1': quote1.attr('data-id'),
		'id2': quote2.attr('data-id'),
		'score1': a === 1 ? 1: 0,
		'score2': a === 2 ? 1: 0,
        'swipe': swipe
	}).then(function (response) {
        update(response.data, true);
    });
	load()
}

function update(data, show){
    $('#quote1').attr('data-id', data[0].id).text(data[0].quote);
    $('#quote2').attr('data-id', data[1].id).text(data[1].quote);
    $('#context1').text(data[0].context);
    $('#context2').text(data[1].context);
    $('.quote-container').css('display', show ? 'block': 'none');
    $(".spinner").css('display', show ? 'none': 'block');
}

function load() {
    update([{id: -1, quote: ''}, {id: -1, quote: ''}], false);
}

function haveAllLoaded() {
    return $("#quote1").attr('data-id') !== '-1' && $("#quote2").attr('data-id') !== '-1';
}

function getOrDefault(urlParams, key, def) {
    let v = urlParams.get(key);
    return v === null ? def: v;
}

function getRankings() {

    let urlParams = new URLSearchParams(window.location.search);
    let dir = getOrDefault(urlParams, 'dir', 'desc');
    let order = getOrDefault(urlParams, 'order', 'rankings');
    let nr = getOrDefault(urlParams, 'nr', 10);
    if (nr === 'all') {
        nr = null;
    }

    axios.post('update.php', {action: 'rankings', 'order': order, 'dir': dir, nr: nr})
        .then(function (response) {
            let template = $('#template').html();
            Mustache.parse(template);
            let data = response.data;
            let matches = data.matches;
            $("#matches").html(matches);
            let ranks = data.ranks;
            let target = $("#pics");
            target.empty();
            for (let i = 0; i < ranks.length; i++) {
                let rank = ranks[i];
                target.append(Mustache.render(template, {
                    rank: i+1,
                    quote: rank.quote,
                    context: rank.context,
                    win_rate: rank.win_rate,
                    rating: rank.rating,
                    matches: rank.matches
                }))
            }
        })
}

function updateData(param, value) {
    let urlParams = new URLSearchParams(window.location.search);
    urlParams.set(param, value);

    if (history.pushState) {
        let path = window.location.href.split('?')[0] + "?" + urlParams.toString();
        window.history.pushState({path: path}, '', path);
        getRankings();
    } else {
        location.search = urlParams.toString();
    }
}

function swipe(id, dir) {
    // swipeDir contains either "none", "left", "right", "top", or "down"
    if (haveAllLoaded() && (dir === "left" || dir === "right")) {
        $("#" + (id === 1 ? "left": "right") + "pane").velocity({
            left: dir === "left" ? -800: 800,
            opacity: 0
        }, {
            duration: 300,
            complete: function (elements) {
                elements.velocity({left: 0, opacity: 1}, {
                    duration: 0
                });
                change(id, true);
            }
        });
    }
}

// credit: http://www.javascriptkit.com/javatutors/touchevents2.shtml
function swipeDetect(el, callback){
    let touchSurface = el,
        swipeDir,
        startX,
        startY,
        distX,
        distY,
        threshold = 100, //required min distance traveled to be considered swipe
        restraint = 100, // maximum distance allowed at the same time in perpendicular direction
        allowedTime = 300, // maximum time allowed to travel that distance
        elapsedTime,
        startTime,
        handleSwipe = callback || function (swipeDir) {};

    touchSurface.on('touchstart', function(e){
        const touchObj = e.changedTouches[0];
        swipeDir = 'none';
        startX = touchObj.pageX;
        startY = touchObj.pageY;
        startTime = new Date().getTime(); // record time when finger first makes contact with surface
        e.preventDefault();
    });

    touchSurface.on('touchmove', function(e){
        e.preventDefault() // prevent scrolling when inside DIV
    });

    touchSurface.on('touchend', function(e){
        const touchobj = e.changedTouches[0];
        distX = touchobj.pageX - startX; // get horizontal dist traveled by finger while in contact with surface
        distY = touchobj.pageY - startY; // get vertical dist traveled by finger while in contact with surface
        elapsedTime = new Date().getTime() - startTime; // get time elapsed
        if (elapsedTime <= allowedTime){ // first condition for awipe met
            if (Math.abs(distX) >= threshold && Math.abs(distY) <= restraint){ // 2nd condition for horizontal swipe met
                swipeDir = (distX < 0)? 'left' : 'right'; // if dist traveled is negative, it indicates left swipe
            }
            else if (Math.abs(distY) >= threshold && Math.abs(distX) <= restraint){ // 2nd condition for vertical swipe met
                swipeDir = (distY < 0)? 'up' : 'down'; // if dist traveled is negative, it indicates up swipe
            }
        }
        if (swipeDir === 'none') {
            $(touchSurface).trigger('click');
        }
        handleSwipe(swipeDir);
        e.preventDefault()
    })
}