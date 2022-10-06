export default interface BaseResponse<T> {
  status: number;
  message: string|null;
  data: T;
}
