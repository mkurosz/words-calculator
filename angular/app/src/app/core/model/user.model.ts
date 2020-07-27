import { staticImplements } from '../static-implements';
import { ApiModel } from './api-model.interface';
import { Word } from './word.model';

@staticImplements<ApiModel<User>>()
export class User {
  public static deserialize(input: any): User {
    if (!input) {
      return User.createNewUser();
    }

    if (!input.id || !input.ip_address || !input.words) {
      throw new Error('Invalid input for User model');
    }

    return new User(
      input.id,
      input.ip_address,
      Word.deserializeArray(input.words)
    );
  }

  public static deserializeArray(input: any[]): User[] {
    const ret = [];

    for (const item of input) {
      ret.push(User.deserialize(item));
    }

    return ret;
  }

  public static createNewUser(): User {
    return new User(null, null, []);
  }

  constructor(
    public id?: number,
    public ipAddress?: string,
    public words?: Word[]
  ) {
  }

  public toJSON(): object {
    return {
      id: this.id,
      ip_address: this.ipAddress,
      words: this.words
    };
  }
}
