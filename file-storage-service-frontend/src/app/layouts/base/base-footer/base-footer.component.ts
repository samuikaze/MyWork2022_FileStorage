import { ViewportScroller } from '@angular/common';
import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-base-footer',
  templateUrl: './base-footer.component.html',
  styleUrls: ['./base-footer.component.sass']
})
export class BaseFooterComponent implements OnInit {

  constructor(private viewportService: ViewportScroller) { }

  ngOnInit(): void {
  }

  showArrowToTop(): void {
    //
  }

  returnToTop(): void {
    this.viewportService.scrollToPosition([0, 0]);
  }
}
