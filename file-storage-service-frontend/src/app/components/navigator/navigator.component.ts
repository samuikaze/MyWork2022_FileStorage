import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, NavigationEnd, Router } from '@angular/router';
import { distinctUntilChanged, filter, map } from 'rxjs';
import Breadcrumb from 'src/app/abstracts/commons/breadcrumb';
import { BreadcrumbService } from 'src/app/services/breadcrumb-service/breadcrumb.service';

@Component({
  selector: 'app-navigator',
  templateUrl: './navigator.component.html',
  styleUrls: ['./navigator.component.sass']
})
export class NavigatorComponent implements OnInit {

  public breadcrumbs: Breadcrumb[] = [];
  constructor(
    private router: Router,
    private breadcrumbService: BreadcrumbService
  ) { }

  ngOnInit(): void {
    this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        const intervalId = setInterval(() => {
          this.getBreadcrumb();
          clearInterval(intervalId);
        }, 0);
      }
    });
  }

  /**
   * 取得目前麵包屑
   */
  public getBreadcrumb(): void {
    this.breadcrumbs = this.breadcrumbService.getBreadcrumb();
  }

  /**
   * 確認是否為最後一筆麵包屑
   * @param index 索引
   * @returns 是否為最後一筆麵包屑
   */
  public isLastBreadcrumb(index: number): boolean {
    return index == (this.breadcrumbs.length - 1);
  }
}
