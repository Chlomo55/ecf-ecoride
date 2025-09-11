<?php include_once 'header.php'; ?>
<section id="mainContent" style="opacity:0;transform:translateY(30px);transition:opacity 0.7s, transform 0.7s;">
<button id="generateTablesBtn" style="margin-bottom:20px;padding:10px 18px;font-size:1.1em;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;">Générer les tables SQL</button>
<div id="sqlResult" style="margin-bottom:20px;font-weight:bold;"></div>
<p>Lorem ipsum habemus papam, consectetur adipiscing elit. Donec vel magna ut ipsum bibendum tincidunt quis vitae ante. Cras laoreet odio a metus faucibus molestie nec et nisl. Maecenas venenatis ligula eu turpis pellentesque, eu consectetur ante tincidunt. Mauris lacinia augue sodales ligula rutrum, eget elementum nisl pharetra. Nullam ut laoreet turpis. Nam in bibendum eros. Praesent et condimentum magna. Quisque sodales tortor vel odio pretium, eget pharetra mauris sodales. Donec tempus nisl a finibus sollicitudin.</p>

<p>Vivamus consequat malesuada diam, sed laoreet odio placerat eu. Nam euismod efficitur sem eget feugiat. Nunc sed nunc justo. In sit amet elit eleifend, fringilla arcu vitae, efficitur mauris. In sit amet posuere diam. Nullam viverra lacinia placerat. Nulla non congue velit, ac mattis metus. Cras at nunc eu tortor efficitur hendrerit.</p>

<p>Proin ullamcorper tempus velit a consequat. Mauris dignissim augue ut sagittis pellentesque. Donec porttitor ligula ut arcu suscipit sollicitudin. Nullam in pulvinar dolor. Donec vehicula, velit sed porta ultrices, elit lectus convallis enim, in aliquam nisi nulla at ipsum. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Curabitur auctor finibus feugiat. In sagittis, ante quis ultricies iaculis, dui arcu sagittis massa, nec iaculis lacus massa et magna. Praesent et lorem viverra augue accumsan porttitor. Cras consectetur, magna vel venenatis fringilla, ex libero porttitor augue, vel tempus elit erat nec lacus. Proin facilisis sed nibh vel lacinia. Cras a nunc nec elit vulputate rutrum non sed arcu. Sed sed sollicitudin velit.
</p>
<p>Ut eu tincidunt diam, sit amet ultrices urna. Nulla id placerat nibh, a cursus quam. Aliquam erat volutpat. In tortor arcu, lacinia et blandit venenatis, lobortis eget mauris. Integer ut lacus sed orci efficitur ullamcorper eget eu eros. In in felis efficitur, molestie urna eget, efficitur lacus. Nunc ut ligula dapibus, imperdiet velit vitae, convallis purus. Proin interdum nisi ac nisi dictum, accumsan venenatis felis luctus. Phasellus sed sapien sapien. Proin nec vehicula lorem. Aliquam aliquam maximus mauris vitae elementum. Proin sed libero quam. Nulla eget fringilla felis.</p>

<p>Aenean at tincidunt erat. Nulla malesuada posuere velit ut posuere. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed feugiat augue ac suscipit venenatis. Donec sapien sem, efficitur sed urna eu, congue suscipit diam. Curabitur vel tristique augue, sit amet faucibus lacus. Praesent consequat libero non massa luctus hendrerit. Nunc suscipit erat nibh, dignissim pharetra odio blandit eu. Fusce non libero semper, congue diam hendrerit, ultrices velit. Suspendisse elit turpis, cursus in est sit amet, dictum scelerisque erat. Quisque porttitor quam lorem, eleifend volutpat nibh volutpat eget. Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
<script>
document.getElementById('generateTablesBtn').addEventListener('click', function(){
	var btn = this;
	btn.disabled = true;
	btn.textContent = 'Génération en cours...';
	fetch('generate_tables.php')
		.then(r=>r.json())
		.then(data=>{
			document.getElementById('sqlResult').textContent = data.success ? 'Tables créées avec succès !' : 'Erreur : ' + data.error;
			btn.textContent = 'Générer les tables SQL';
			btn.disabled = false;
		})
		.catch(()=>{
			document.getElementById('sqlResult').textContent = 'Erreur lors de la requête.';
			btn.textContent = 'Générer les tables SQL';
			btn.disabled = false;
		});
});
</script>
</section>
<script>
document.addEventListener('DOMContentLoaded', function(){
	setTimeout(function(){
		var main = document.getElementById('mainContent');
		if(main){
			main.style.opacity = 1;
			main.style.transform = 'translateY(0)';
		}
	}, 200);
});
</script>