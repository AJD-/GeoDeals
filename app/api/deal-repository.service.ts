import { Injectable } from '@angular/core';
import { Http, Headers, Response } from '@angular/http';

import { Deal } from './deal';

@Injectable()
export class DealRepository {

	private _deals: Deal[];

	private getIndex(id : number){
		for (var i = this._deals.length; i--;) {
			var deal = this._deals[i];
			if(deal.id == id) return i;
		}
		return -1;
	}

	constructor(){
		this._deals = [
			{ id: 1, title: 'Deal 1', description: "SAMPLE DESCRIPTION", imagePath: 'img/tilted.png', rating: 20 },
			{ id: 2, title: 'Deal 2', description: "SAMPLE DESCRIPTION", imagePath: 'img/unknown.png', rating: 35 },
			{ id: 3, title: 'Deal 3', description: "SAMPLE DESCRIPTION", imagePath: 'img/wat.png', rating: 10 }
		];
	}

	public list() : Deal[] {
		return this._deals;
	}

	public get(id : number) : Deal {
		var index = this.getIndex(id);
		return this._deals[index];
	}

	public add(deal: Deal) {
		deal.id = this._deals.length + 1;
		this._deals.push(deal);
	}

	public update(deal: Deal) {
		var index = this.getIndex(deal.id);
		this._deals[index] = deal;
	}

	public delete(id : number) {
		var index = this.getIndex(id);
		this._deals.splice(index, 1);
	}
}