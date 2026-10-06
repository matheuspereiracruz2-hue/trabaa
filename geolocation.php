<?php
/** geolocation.php - "Mapa da Vila dos Pelicanos": usa o GPS do navegador (lógica em assets/js/geolocation.js). */
$titulo = 'Mapa da Vila';
$pagina = 'mapa';
$scripts_extra = ['assets/js/geolocation.js'];
require __DIR__ . '/includes/header.php';
?>
<h1>Mapa da Vila dos Pelicanos</h1>
<p>Descubra a que distância você está da vila. Seu navegador vai pedir permissão para acessar a localização; ela é usada só nesta página e não é enviada ao servidor.</p>

<section class="painel">
  <button class="botao" id="btn-localizar" type="button">Descobrir minha posição</button>
  <p id="resultado" class="resultado" aria-live="polite">Clique no botão para começar a viagem.</p>
  <p id="coordenadas" class="meta"></p>
</section>

<section class="painel">
  <h2>Mapa do vale</h2>
  <div class="mapa" id="mapa" role="img" aria-label="Mapa ilustrado do vale com a vila no centro">
    <span class="poi" style="left:50%;top:50%">Vila dos Pelicanos</span>
    <span class="poi" style="left:22%;top:72%">Sua Fazenda</span>
    <span class="poi" style="left:78%;top:22%">Minas</span>
    <span class="poi" style="left:50%;top:88%">Praia</span>
    <span class="poi" style="left:20%;top:20%">Floresta</span>
    <span class="jogador" id="jogador" hidden>Você</span>
  </div>
  <p class="meta">O marcador "Você" aparece na direção real (norte no topo) em que você está em relação à vila. Quanto mais longe, mais perto da borda.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
