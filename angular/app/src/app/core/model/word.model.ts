import { staticImplements } from '../static-implements';
import { ApiModel } from './api-model.interface';

@staticImplements<ApiModel<Word>>()
export class Word {
  public static deserialize(input: any): Word {
    if (!input.id || !input.word || !input.count) {
      throw new Error('Invalid input for Word model');
    }

    return new Word(
      input.id,
      input.word,
      input.count
    );
  }

  public static deserializeArray(input: any[]): Word[] {
    const ret = [];

    for (const item of input) {
      ret.push(Word.deserialize(item));
    }

    return ret;
  }

  public static createNewWord(): Word {
    return new Word(null, null, 0);
  }

  constructor(
    public id: number,
    public word: string,
    public count: number
  ) {
  }

  public toJSON(): object {
    return {
      id: this.id,
      word: this.word,
      count: this.count
    };
  }
}
