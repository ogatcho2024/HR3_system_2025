<section class="slider_section">
  <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
    <div class="indicator_box">
      <ol class="carousel-indicators">
        <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active" style="color: var(--read-more);">01</li>
        <li data-target="#carouselExampleIndicators" data-slide-to="1" style="color: var(--read-more);">02</li>
        <li data-target="#carouselExampleIndicators" data-slide-to="2" style="color: var(--read-more);">03</li>
        <li data-target="#carouselExampleIndicators" data-slide-to="3" style="color: var(--read-more);">04</li>
      </ol>
      <div class="carousel-slide" style="color: var(--read-more);"><span>/04</span></div>
    </div>
    <div class="carousel-inner">
      @foreach ([
          ['Monitor', 'Crane Usage', 'Efficiently', 'firstpic.png', '#listjobs'],
          ['Track', 'Maintenance', 'Schedules', 'secondpic.png', '#featuredjob'],
          ['Optimize', 'Workflows', 'with Insights', 'thirdpic.png', '#developersquote'],
          ['About', 'Our', 'System', 'fourthpic.png', '#aboutus']
      ] as $index => $slide)
        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" data-bg="{{ ['#111111', '#bebdbd', '#111111', '#bebdbd'][$index] }}" data-bg-right="{{ ['#bebdbd', '#111111', '#bebdbd', '#111111'][$index] }}" 
          data-main-text="{{ ['#ffffff', '#111111', '#ffffff', '#111111'][$index] }}" data-slider-page="{{ ['#111111', '#ffffff', '#111111', '#ffffff'][$index] }}" data-read-more="{{ ['#000000', '#ffffff', '#000000', '#ffffff'][$index] }}">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-4 offset-md-1">
                <div class="detail-box">
                  <h1>{!! implode('<br>', array_slice($slide, 0, 3)) !!}</h1>
                  <div><a href="{{ $slide[4] }}" style="color: var(--read-more);">Read More</a></div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="img-box">
                  <img src="{{ asset('images/' . $slide[3]) }}" alt="">
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
        <i class="fas fa-chevron-left fa-2x text-white"></i>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
        <i class="fas fa-chevron-right fa-2x text-white"></i>
        <span class="sr-only">Next</span>
    </a>
  </div>
</section>