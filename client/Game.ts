define([
  'dojo',
  'dojo/_base/declare',
  'ebg/core/gamegui',
  'ebg/counter',
  'ebg/stock',
  'ebg/zone',
], (dojo, declare) => {
  declare('bgagame.effortlesswc', ebg.core.gamegui, new GameBody());
});
