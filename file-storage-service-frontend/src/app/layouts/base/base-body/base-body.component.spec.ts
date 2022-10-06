import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BaseBodyComponent } from './base-body.component';

describe('BaseBodyComponent', () => {
  let component: BaseBodyComponent;
  let fixture: ComponentFixture<BaseBodyComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ BaseBodyComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(BaseBodyComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
