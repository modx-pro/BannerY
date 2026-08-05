<div class="bannerpro-demo-product border rounded p-2 mb-2" data-banner-id="[[+id]]" data-adposition="[[+adposition]]">
  <a href="[[++bannerpro_click]]/[[+adposition]]" class="text-decoration-none">
    [[+product_id:gt=`0`:and:product_thumb:notempty=`
      <img src="[[+product_thumb]]" alt="[[+product_title]]" loading="lazy" class="img-fluid rounded mb-1" />
    `]]
    [[+product_id:eq=`0`:and:image:notempty=`
      <img src="[[+image]]" alt="[[+name]]" title="[[+description]]" loading="lazy" class="img-fluid rounded mb-1" />
    `]]
    <strong>[[+product_id:gt=`0`:and:product_title:notempty=`[[+product_title]]`:else=`[[+name]]`]]</strong>
    [[+description:notempty=`<div class="small text-muted">[[+description]]</div>`]]
    [[+product_id:gt=`0`:and:product_price:notempty=`<div class="small">[[+product_price]]</div>`]]
    [[+product_id:gt=`0`:and:product_old_price:notempty=`<div class="small text-muted"><s>[[+product_old_price]]</s></div>`]]
    <div class="small text-muted mt-1">#[[+id]] · pos [[+adposition]] · [[+type]][[+product_id:gt=`0`:then=` · product [[+product_id]]`]]</div>
  </a>
</div>
