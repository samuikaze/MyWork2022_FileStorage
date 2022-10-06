import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { BaseHeaderComponent } from './layouts/base/base-header/base-header.component';
import { BaseBodyComponent } from './layouts/base/base-body/base-body.component';
import { BaseFooterComponent } from './layouts/base/base-footer/base-footer.component';
import 'bootstrap/dist/js/bootstrap.bundle.js';
import { HomeComponent } from './components/home/home.component';
import { NavigatorComponent } from './components/navigator/navigator.component';
import { LightboxModule } from 'ngx-lightbox';
import { HttpClientModule } from '@angular/common/http';
import { FileUploadComponent } from './components/file-upload/file-upload.component';
import { FormsModule } from '@angular/forms';

@NgModule({
  declarations: [
    AppComponent,
    BaseHeaderComponent,
    BaseBodyComponent,
    BaseFooterComponent,
    HomeComponent,
    NavigatorComponent,
    FileUploadComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    LightboxModule,
    HttpClientModule,
    FormsModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
