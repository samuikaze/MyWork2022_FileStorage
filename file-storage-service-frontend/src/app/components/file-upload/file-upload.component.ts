import { Component, Input, OnInit } from '@angular/core';
import { FileUploadService } from 'src/app/services/file-upload-service/file-upload.service';

@Component({
  selector: 'app-file-upload',
  templateUrl: './file-upload.component.html',
  styleUrls: ['./file-upload.component.sass']
})
export class FileUploadComponent implements OnInit {

  @Input() singleFile: File[] = [];
  public progress: number = 0;
  constructor(private fileUploadService: FileUploadService) { }

  ngOnInit(): void {
  }

  public singleFileUpload() {
    if (this.singleFile.length > 0) {
      this.singleFile.forEach(async file => {
        await this.fireChunkUpload(file);
        this.fileUploadService.mergeFile(file.name);
      });

      this.singleFile = [];
    }
  }

  protected async fireChunkUpload(file: File) {
    const chunkQty = this.fileUploadService.calculateChunkQty(file);

    for (let qty = 0; qty < chunkQty; qty++) {
      const contentType = file.type;
      const chunk = this.fileUploadService.getChunk(file, qty);
      const isLast = qty === (chunkQty - 1);
      try {
        const result = await this.fileUploadService.chunkUploadFiles(file.name, chunk, qty, isLast);
        if (result) {
          this.progress = Math.ceil(((qty + 1) / chunkQty) * 100);
        }
      } catch (error) {
        alert(error);
      }
    }
  }

  public appendFile(event: Event) {
    const element = (event.target as HTMLInputElement);
    if (element != null && element.files != null) {
      this.singleFile.push(element.files[0]);
    }
  }
}
