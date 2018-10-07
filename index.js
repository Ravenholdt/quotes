function start(){
	axios.post('/update.php', {'action': 'start'})
		.then(function (response) {
			update(response.data);
        });
}

function change(a){
    let img1 = $('#img1');
    let img2 = $('#img2');
    let l1 = img1.attr('data-l');
    let l2 = img2.attr('data-l');
    if (l1 !== 'true' && l2 !== 'true') {
		return;
    }
	axios.post('/update.php', {
		'action': 'change',
		'id1': img1.attr('data-id'),
		'id2': img2.attr('data-id'),
		'score1': a === 1 ? 1: 0,
		'score2': a === 2 ? 1: 0
	}).then(function (response) {
        update(response.data);
    });
	load()
}

function update(data){
	$('#img1').attr('data-id', data[0].id).attr('src', data[0].src);
    $('#img2').attr('data-id', data[1].id).attr('src', data[1].src);
}

function load() {
    update([{id: -1, src: '#'}, {id: -1, src: '#'}]);
    $('#img1').attr('data-l', false).css('display', 'none');
    $('#img2').attr('data-l', false).css('display', 'none');
    $(".spinner").css('display', 'block');
}

function loaded(id) {
	let img1 = $('#img1');
	let img2 = $('#img2');
	let l1 = img1.attr('data-l');
    let l2 = img2.attr('data-l');
    if ((l1 === 'true' && l2 === 'false') || (l1 === 'false' && l2 === 'true')) {
    	$(".spinner").css('display', 'none');
        img1.attr('data-l', true).css('display', 'block');
        img2.attr('data-l', true).css('display', 'block');
	} else {
        $('#img' + id).attr('data-l', true);
	}
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
                    src: rank.src,
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
