import { HttpErrorResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { env } from 'src/app/environments/env';
import { environment } from 'src/environments/environment';
import { RequestService } from '../request-service/request.service';

@Injectable({
  providedIn: 'root'
})
export class FileUploadService {

  constructor(private requestService: RequestService) { }

  /**
   * 分塊上傳檔案
   * @param filename 檔案名稱
   * @param chunk 分塊
   * @param time 次數
   * @param isLast 是否為最後一塊
   * @returns 是否成功
   */
  public async chunkUploadFiles(filename: string, chunk: Blob, time: number, isLast: boolean) {
    const url = `${environment.backend}/api/v1/file/chunk`;
    let body = new FormData();
    body.append("filename", filename);
    body.append("chunk", chunk);
    body.append("count", time.toString());
    body.append("isLast", isLast.toString());
    if (! await this.chunkUpload(url, body)) {
      throw new Error("Upload failed");
    }

    return true;
  }

  /**
   * 呼叫 API 讓後端合併分塊為檔案
   * @param filename 檔案名稱
   */
  public mergeFile(filename: string) {
    const mergeUrl = `${environment.backend}/api/v1/file/chunk/merge`;
    const mergeBody = {filename: filename};
    this.requestService.post(mergeUrl, mergeBody)
      .subscribe();
  }

  /**
   * 上傳分塊
   * @param url 後端網址
   * @param body 請求酬載
   * @returns 呼叫結果
   */
  private async chunkUpload(url: string, body: FormData): Promise<boolean|string> {
    return new Promise((resolve, reject) => {
      this.requestService.post(url, body)
        .subscribe({
          next: () => { resolve(true); },
          error: (error: HttpErrorResponse) => { reject(error.message); }
        });
    });
  }

  /**
   * 檔案切塊
   * @param file 原始檔案
   * @param time 次數
   * @returns 切塊檔案
   */
  public getChunk(file: File, time: number): Blob {
    const start = time * env.CHUNK_SIZE;
    const end = start + env.CHUNK_SIZE;

    return file.slice(start, end);
  }

  /**
   * 計算切塊數量
   * @param file 原始檔案
   * @returns 切塊數量
   */
  public calculateChunkQty(file: File): number {
    return Math.ceil(file.size / env.CHUNK_SIZE);
  }
}
