import { Injectable } from '@angular/core';
import { Title } from '@angular/platform-browser';
import { ActivatedRoute } from '@angular/router';
import { IAlbum, Lightbox } from 'ngx-lightbox';

@Injectable({
  providedIn: 'root'
})
export class CommonService {

  private siteTitle = "範例專案";
  constructor(
    private route: ActivatedRoute,
    private titleService: Title,
    private lightbox: Lightbox
  ) { }

  /**
   * 設定頁面標題
   * @param newTitle 新標題
   */
  public setTitle(newTitle: string): void {
    if (newTitle.length > 0) {
      this.titleService.setTitle(`${newTitle} - ${this.siteTitle}`);
    } else {
      this.titleService.setTitle(this.siteTitle);
    }
  }

  /**
   * 處理日期時間
   *
   * @param raw 原始值
   * @param num 要返回的個數
   * @returns 處理後的值
   */
  public processDateTime(raw: string, num: number): string {
    if (num > 6) {
      num = 6;
    } else if (num < 0) {
      num = 0;
    }

    const rawDate = new Date(raw);
    const year = rawDate.getFullYear();
    const month = rawDate.getMonth() + 1;
    const date = rawDate.getDate();
    const hour = rawDate.getHours();
    const minute = rawDate.getMinutes();
    const seconds = rawDate.getSeconds();

    const dateArray = [year, month, date, hour, minute, seconds];
    const separator = ["-", "-", " ", ":", ":"];
    let result = "";
    for (let i = 0; i < num; i++) {
      if (i != 0) {
        result += `${separator[i - 1]}`;
      }

      result += (dateArray[i].toString().length < 2) ? `0${dateArray[i]}` : `${dateArray[i]}`;
    }

    return result;
  }

  /**
   * 處理 IAlbum 物件
   * @param raw 原始 img 標籤
   * @param thumb 縮圖網址
   * @param caption 替代文字
   * @param downloadUrl 下載網址
   * @returns IAlbum 物件
   */
  generateIAlbumObject(
    raw: HTMLImageElement,
    thumb?: string,
    caption?: string,
    downloadUrl?: string
  ): IAlbum {
    if (thumb == undefined) {
      thumb = raw.src;
    }

    if (downloadUrl == undefined) {
      downloadUrl = raw.src;
    }

    const I_ALBUM: IAlbum = {
      src: raw.src,
      caption: caption,
      thumb: thumb,
      downloadUrl: downloadUrl,
    };

    return I_ALBUM;
  }

  /**
   * 打開燈箱
   * @param iAlbum 多個 IAlbum 物件
   * @param index 打開第幾張圖
   */
   openLightbox(iAlbum: Array<IAlbum>, index: number): void {
    this.lightbox.open(iAlbum, index);
  }
}
