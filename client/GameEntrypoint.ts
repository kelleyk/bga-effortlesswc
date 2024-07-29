console.log('*** top of GameEntrypoint.ts');
define([
  'dojo',
  'dojo/_base/declare',
  'ebg/core/gamegui',
  'ebg/counter',
  'ebg/stock',
  'ebg/zone',
], (_dojo: any, declare: any) => {
  // console.log('*** define() in GameEntrypoint.ts');
  // const x = declare(ebg.core.gamegui, new GameBody());
  // ((window as any).bgagame ??= {}).effortless = x;
  // return x;

  return declare('bgagame.effortless', ebg.core.gamegui, new GameBody());
});
