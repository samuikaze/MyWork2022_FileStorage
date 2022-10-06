# Angular 13 範例專案

此專案為前端範例專案，為節省新專案安裝與設定相關套件的時間，因此增加此範例專案

## 相關功能啟用說明

由於不是所有的功能都需要每次都啟用，因此部分功能是以註解的方式先行註解掉，有需要再打開註解進行注入與設定即可

### 麵包屑

如需啟用麵包屑相關功能，依據下列順序啟功此功能:
- 打開 `app.component.html` 並打開 `<app-navigator></app-navigator>` 的註解
- 於各 Components 的 ts 檔注入 `BreadcrumbService`，並新增以 `Breadcrumb` 為型別的 breadcrumb 屬性
- 於 `ngOnInit()` 進行 `BreadcrumbService` 中相關方法的呼叫

### 表單雙向綁定

如需表單雙向綁定相關功能，打開 `app.module.ts`，並打開 `FormsModule` 模組的注入後即可使用
