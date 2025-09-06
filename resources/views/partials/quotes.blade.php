<section class="client_section" id="developersquote">
  <div class="container layout_padding">
    <div class="heading_container"><h2>Developer's Quotes</h2></div>
    <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
      <div class="carousel_btn-container">
        <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
          <span class="sr-only">Next</span>
        </a>
      </div>
      <div class="carousel-inner">
        @foreach ([
          ['stryker.jpg', 'Styker John De Guzman', 'A website is not just a digital presence...'],
          ['lapid.jpg', 'Sherwin Mae G. Lapid', 'Good code is invisible. The better a developer...'],
          ['estorba.jpg', 'Mark John M. Estorba', 'Behind every well-designed website lies...']
        ] as $index => [$img, $name, $quote])
          <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
            <div class="box">
              <div class="img-box">
                <img src="{{ asset('images/' . $img) }}" alt="">
              </div>
              <div class="detail-box">
                <h5>{{ $name }}</h5>
                <p>{{ $quote }}</p>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>