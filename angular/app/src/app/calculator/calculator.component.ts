import { Component, OnInit } from '@angular/core';
import { ApiError, User, Word } from '../core/model';
import { UserService } from '../core';
import { MatTableDataSource } from "@angular/material/table";

@Component({
  selector: 'app-calculator',
  templateUrl: './calculator.component.html',
  styleUrls: ['./calculator.component.scss'],
})
export class CalculatorComponent implements OnInit {
  public user: User = User.createNewUser();
  public text: string = '';
  public lastSubmittedText: string = '';
  public inProgress: boolean = false;
  public formSubmited: boolean = false;
  public sendError?: string = null;
  public dataSource = new MatTableDataSource<Word>();
  public displayedColumns: string[] = ['no', 'word', 'count', 'stars'];

  constructor(
    private userService: UserService,
  ) {
  }

  public ngOnInit(): void {
    this.initUser();
  }

  public calculateNumberOfWords(): number {
    let str = this.text.replace(/(^\s*)|(\s*$)/gi,"");
    str = str.replace(/[ ]{2,}/gi," ");
    str = str.replace(/\n /,"\n");

    return str.split(' ').filter((word: string) => {
      return word.length > 2
    }).length;
  }

  public sendText() {
    if (!this.text || this.text === '') {
      return;
    }

    this.inProgress = true;
    this.sendError = null;
    this
      .userService
      .postUserWords(this.text)
      .subscribe(
        (words: Word[]) => {
          this.dataSource.data = words;
          this.lastSubmittedText = this.text;
          this.text = '';
          this.inProgress = false;
          this.formSubmited = true;
        },
        (data: ApiError) => {
          this.inProgress = false;
          this.formSubmited = false;
          this.sendError = data.message;
        },
      );
  }

  public restoreForm() {
    this.clearResults();
    this.formSubmited = false;
  }

  private initUser(): void {
    this
      .userService
      .getUser()
      .subscribe(
        (user: User) => {
          this.user = user;
        },
        (data: ApiError) => {
          this
            .userService
            .postUser()
            .subscribe(
              (user: User) => {
                this.user = user;
              }
            )
        },
      );
  }

  private clearResults(): void {
    this.dataSource.data = [];
  }
}
