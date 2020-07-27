import { catchError, map } from 'rxjs/operators';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { User, Word } from './model';

@Injectable()
export class UserService {

  constructor(
    private api: ApiService
  ) {
  }

  public getUser(): Observable<User> {
    return this
      .api
      .get('user')
      .pipe(
        map((result: any) => {
          return User.deserialize(result.body);
        }),
        catchError((error: any) => this.api.prepareFormErrors(error))
      );
  }

  public postUser(): Observable<User> {
    return this
      .api
      .post('users', {})
      .pipe(
        map((result: any) => {
          return User.deserialize(result.body);
        }),
        catchError((error: any) => this.api.prepareFormErrors(error))
      );
  }

  public postUserWords(text: string): Observable<Word[]> {
    return this
      .api
      .post('users/words', { text: text })
      .pipe(
        map((result: any) => {
          return Word.deserializeArray(result.body);
        }),
        catchError((error: any) => this.api.prepareFormErrors(error))
      );
  }
}
