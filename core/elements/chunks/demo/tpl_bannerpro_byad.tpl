<article class="bannerpro-demo-ad mb-2 p-2 border rounded" data-banner-id="[[+id]]" data-adposition="[[+adposition]]">
  <a href="[[++bannerpro_click]]/[[+adposition]]" class="d-block text-decoration-none">
    [[+image:notempty=`
      <img src="[[+image]]" alt="[[+name]]" title="[[+description]]" loading="lazy" class="img-fluid rounded" />
    `:default=`
      <strong>[[+name]]</strong>
    `]]
    [[+description:notempty=`<div class="small text-muted mt-1">[[+description]]</div>`]]
    <div class="small text-muted mt-1">#[[+id]] · pos [[+adposition]] · [[+type]]</div>
  </a>
</article>
