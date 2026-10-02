-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 07, 2026 at 12:56 AM
-- Server version: 5.7.23-23
-- PHP Version: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `howard_tms`
--

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `bg_color` varchar(20) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `action_url` varchar(255) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `icon`, `bg_color`, `is_read`, `created_at`, `action_url`, `related_id`) VALUES
(1, 2200562, NULL, 'A new asset (OE-15001-RVG001) has been assigned to you.', 'fa-box', 'info', 0, '2026-03-26 01:47:37', '//employee/assets', 25),
(2, 2200426, NULL, 'New IT Ticket Filed', 'fa-ticket-alt', 'primary', 0, '2026-03-26 01:48:21', '/admin/tickets', 86),
(3, 2200428, NULL, 'New IT Ticket Filed', 'fa-ticket-alt', 'primary', 0, '2026-03-26 01:48:21', '/it/tickets', 86),
(4, 2200458, NULL, 'New IT Ticket Filed', 'fa-ticket-alt', 'primary', 1, '2026-03-26 01:48:21', '/admin/tickets', 86),
(5, 2200558, NULL, 'New IT Ticket Filed', 'fa-ticket-alt', 'primary', 0, '2026-03-26 01:48:21', '/it/tickets', 86),
(6, 2200559, NULL, 'New IT Ticket Filed', 'fa-ticket-alt', 'primary', 0, '2026-03-26 01:48:21', '/it/tickets', 86),
(7, 2200568, NULL, 'New IT Ticket Filed', 'fa-ticket-alt', 'primary', 0, '2026-03-26 01:48:21', '/head/employee', 86),
(8, 2200562, NULL, 'Your ticket STM-20260326-0086 has been APPROVED.', 'fa-check-circle', 'success', 0, '2026-03-26 01:49:29', '//employee/tickets', 86),
(9, 2200562, NULL, 'Your ticket has been resolved. Click to rate IT support.', 'fa-star', 'success', 0, '2026-03-26 01:51:30', '//employee/tickets/rate?id=86', 86);

-- --------------------------------------------------------

--
-- Table structure for table `tblaccounts`
--

CREATE TABLE `tblaccounts` (
  `account_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `usertype` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `createdby` varchar(50) NOT NULL,
  `datecreated` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblaccounts`
--

INSERT INTO `tblaccounts` (`account_id`, `username`, `password`, `usertype`, `status`, `createdby`, `datecreated`) VALUES
(2200425, 'smiran.rolandjosh', '123', 'EMPLOYEE', 'ACTIVE', 'admin', '10/27/2025'),
(2200426, 'admin', '123', 'ADMIN', 'ACTIVE', 'josh', '10/27/2025'),
(2200428, 'smiran.kenneth', '$2y$10$BmbuvdIMg5bIs58JdG6uQei3vIextx1olj0CqPx23dRy7.7.3hAla', 'IT', 'ACTIVE', 'test', '10/28/2025'),
(2200454, 'smiran.employee', '$2y$10$g97KaTRp.DuAGwUSzb03lO3c2pTKnTf9UXYnGYjtGANSWQBQuZSay', 'EMPLOYEE', 'ACTIVE', 'admin', '2025-12-18 08:08:18'),
(2200455, 'smiran.head', '$2y$10$eZngRtNTP2t5OFAE2WcIZ.w2MmQ0q0ZdXZnJh4hTCHDSZCGFvX/bm', 'HEAD', 'ACTIVE', 'admin', '2025-12-18 08:09:42'),
(2200458, 'storagemart.kennethdador@gmail.com', '$2y$10$ZrmpjbDqdwonmioINpkV4.7fM5hdOt7AayCEQ/NScxPT8k5XpeChS', 'ADMIN', 'ACTIVE', 'admin', '2026-03-23 01:10:01'),
(2200497, 'storagemart.danmangaban@gmail.com', '$2y$10$oJVM6c8pV0BKKvawoG0V8.7qRRIScO8ex/9GvMiMdxqKxLhgj.nSe', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 03:52:19'),
(2200498, 'storagemart.annabueva@gmail.com', '$2y$10$YgyFcupTc7SMtiW7iUr4leiQb0jz3F2NlHKGXoepTYoukUNqOyyeS', 'HEAD', 'ACTIVE', 'Kenneth', '2026-03-23 03:55:43'),
(2200499, 'storagemart.julieantangunan@gmail.com', '$2y$10$wvaDIPexQfwoDo9UsasNweU.LV4BoI8LBvimwDwOZC.dDQjD8xrgi', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 03:58:13'),
(2200500, 'storagemart.marielobnamia@gmail.com', '$2y$10$oGL2xmDRmQkkz6ZlZ537a.OLODJEtxHP4E.0J46YUGUVd6TntNwUW', 'EMPLOYEE', 'ACTIVE', 'admin', '2026-03-23 03:58:36'),
(2200501, 'storagemart.sambernal@gmail.com', '$2y$10$DwC3B4C7VsfXL.BTre/hdug5FR4aAU6Vgfu1b3JE8NjrEZ25lafy6', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 03:59:37'),
(2200502, 'storagemart.charrisebordon@gmail.com', '$2y$10$GqVJcuzwX9SLPL47eU9g8.crjD.AqZSaSGkqqhAxq6zoLcKzZX1AS', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:01:09'),
(2200503, 'storagemart.karljose@gmail.com', '$2y$10$AdlBMT87QTxEn9JlLX4gfuocTH2ojU1kiHNadfytCxcX42bswN5gy', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:02:39'),
(2200504, 'storagemart.rosemadla@gmail.com', '$2y$10$UXHTKof9ntoiUN0D4Lh7uuOsf1heVwSnuxhcIjPF5s/enY.fsDZh.', 'HEAD', 'ACTIVE', 'Kenneth', '2026-03-23 04:03:50'),
(2200505, 'storagemart.christianramos@gmail.com', '$2y$10$MJnmTzNh.UZNBTzdkOl5reFECu4Un4SC6kJciZYztJ9h5RRzg7JPG', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:05:11'),
(2200506, 'storagemart.antonioramosjr@gmail.com', '$2y$10$eMZbTJ/T9QauL33qILAmQuhg2ocusigJfjWFbf7iHledpJxoyPR7K', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:06:33'),
(2200507, 'storagemart.sylenenuqui@gmail.com', '$2y$10$MvhdaPX/1J3cOsM.Ycs7sOGBDesr6/41vGa7b/jti1glvCBZT/a1y', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:07:43'),
(2200508, 'storagemart.maryjanelingues@gmail.com', '$2y$10$u3TQD3os08z1Q6sz7phGxO2hctP/XfO8ini4jLtTXtftAeuE78Ejy', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:08:49'),
(2200509, 'storagemart.kryzlebanzuela@gmail.com', '$2y$10$5biwdfut3XKBde0phok7LOXNDlzP4kgSFy17NcyZLRyakqqR/L05C', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:10:19'),
(2200510, 'storagemart.geraldinebraceros@gmail.com', '$2y$10$DuR6Xh9OwHX3COSsJKGyy.7G6sj.N3tdJxvpf07lX1WNnOfiXGxFS', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:11:40'),
(2200511, 'storagemart.lesterronquillo@gmail.com', '$2y$10$o7hd8EX38Klq2XoJyGPkeewgFby6zKCQrqMz32r/Mlj9wg51vrPZa', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:12:42'),
(2200512, 'storagemart.alferpradojr@gmail.com', '$2y$10$0vxZGhdKvPKylVkwIrWSP.0e1uEiOdzzAVRvSAx28xiwXtIx/DNiy', 'EMPLOYEE', 'ACTIVE', 'admin', '2026-03-23 04:14:05'),
(2200513, 'storagemart.francisdomingo@gmail.com', '$2y$10$1Ox6imNko.rxPJ/e.bS1VuCEz7g9b3Ee3kZ9Vq4zS8wPAQlUOQBeC', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:14:22'),
(2200514, 'storagemart.laurenarias@gmail.com', '$2y$10$.2RV2PfdeosFREfcY2Yo.uPRtyCuvoTl/YKLf39PRPYkjrm0aMMMe', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:15:32'),
(2200515, 'storagemart.johannareyes@gmail.com', '$2y$10$TcME//Yypy8E153NyInATOoapAfGYIk8v4VbqrXnVbya9AbNqsIce', 'EMPLOYEE', 'ACTIVE', 'admin', '2026-03-23 04:15:41'),
(2200516, 'storagemart.xyzalopez@gmail.com', '$2y$10$yUUeSPV0T9wSmdwTpbVKpugDXebrzHn9GPgf97FFu/MOYTekP8Sb.', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:16:39'),
(2200517, 'storagemart.patriciatamayo@gmail.com', '$2y$10$vTHe4.k/UdjUaEg7zFDxYOLvRSlHn.XmBdo4TFPTyE2.v1ySGQ4om', 'EMPLOYEE', 'ACTIVE', 'admin', '2026-03-23 04:17:38'),
(2200518, 'storagemart.elvinoriel@gmail.com', '$2y$10$vTpwa1f5gPtLYy/tairLs.YXXUoSkc3tWRRZPmm2JLpXpAqajfvxm', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:17:46'),
(2200519, 'storagemart.marktolentino@gmail.com', '$2y$10$yyhS5Z358AzyYs2ZVh5S/ev.dkEty3bfjSRAJo8Kpgup4XnR8OJ12', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:19:07'),
(2200520, 'storagemart.merylprochina@gmail.com', '$2y$10$fkfMD2XU6rToZmIjmIcS3OuslwqUAMDr8otYDy9H0tgaN5RAFytn2', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:22:13'),
(2200521, 'storagemart.micahbelmagon@gmail.com', '$2y$10$iz0jNb9Itip/4wbkRsHNJeI2WDlXSmVRcnmbmlOVjW7nb3Ma6TUn.', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:25:09'),
(2200522, 'storagemart.patricdelmundo@gmail.com', '$2y$10$88gJJSC7TMG7NIirIdA9lOWgWlh/s.0v/5WDd.zBNsCSENNq1AL4C', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:26:36'),
(2200523, 'storagemart.amelinajuarez@gmail.com', '$2y$10$c3x.b6zL1jYqMghsUqnPD.ae3Z/UIDJKwXUDU.Zy.W3yXnuOUBaYK', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:27:50'),
(2200524, 'storagemart.maevelynbosita@gmail.com', '$2y$10$OdgqsOZeMuTKs5ixLXbbTO7Z4yZGiTL4KMYUMJR.t5Sxchnj6RfRS', 'EMPLOYEE', 'ACTIVE', 'admin', '2026-03-23 04:29:24'),
(2200525, 'storagemart.girlierayon@gmail.com', '$2y$10$n.yKQ7HyjTWxfUMg4vag9OHCTffsFslHo6JqWrPN8uF8WLVDpxhly', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:29:33'),
(2200526, 'storagemart.kylalasquite@gmail.com', '$2y$10$CaLpz5aJ2pZBXBLUupI0b.8j3DSIg.u/VDq5baxPB/GkmRUmDlkDi', 'EMPLOYEE', 'ACTIVE', 'admin', '2026-03-23 04:33:30'),
(2200527, 'storagemart.reynaldroxas@gmail.com', '$2y$10$4N2pwLZvxROe3KPb1VxMGe4QLjvmqiutqrLPtiSgA.48GNW0Rm4u.', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:34:48'),
(2200528, 'storagemart.hannahromero@gmail.com', '$2y$10$dqUtCw26slpO3l5t0/7t/.xzFTRyYHrxXrZj1PQ4NV3HW2Lg3XTlC', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:36:12'),
(2200529, 'storagemart.reynaldpendon@gmail.com', '$2y$10$qZYQc2sS0M3ZTACvDv.8F.hwJpWL8cC8tDcPcmneYiaq47kRmR61u', 'EMPLOYEE', 'ACTIVE', 'admin', '2026-03-23 04:36:38'),
(2200530, 'storagemart.jermalynrevuelta@gmail.com', '$2y$10$lbQQR5JZHRNk3nWklbxoJOHlvzEPYNNopvoGZ1Ys5yWIZ5QqM7i12', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:37:23'),
(2200531, 'storagemart.jusefherignacio@gmail.com', '$2y$10$62kbfSrpP0ZcPAT13A3lFOdD1AzL0kxGngNkAgszMm0cAu0yu/9xi', 'EMPLOYEE', 'ACTIVE', 'admin', '2026-03-23 04:38:24'),
(2200532, 'storagemart.veriellevitug@gmail.com', '$2y$10$VbteNwZ.qv7g7V.LbWuf9OEq9GdsWv/vb3a6wn/EEdJXIXOXI6wVW', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:38:36'),
(2200533, 'storagemart.joanagonzales@gmail.com', '$2y$10$2wiHL/SH4YI4BqRNDvKFTOYasKdOnzlcKc7UrnRQsSAfCLKkM/75e', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:39:39'),
(2200534, 'storagemart.peterusalla@gmail.com', '$2y$10$GocEhv.ahCl6uwtV05p4uOtY5J3biHtCTGunu/SC6dejkuDcMOXkq', 'EMPLOYEE', 'ACTIVE', 'admin', '2026-03-23 04:39:56'),
(2200535, 'storagemart.joshuanicolas@gmail.com', '$2y$10$Osp/3JJw3zC1kBDVA2kUse63bSCkfkT.ftchKDXmRp6uMqCRbwKzG', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:40:43'),
(2200536, 'storagemart.erikapanday@gmail.com', '$2y$10$mpqrLSBztxY6e8SFH0rvSusu.wYgRqlTlPeoHO9xroNY/4opRHyHG', 'EMPLOYEE', 'ACTIVE', 'admin', '2026-03-23 04:41:25'),
(2200537, 'storagemart.leaprudencio@gmail.com', '$2y$10$pF6ejD9iaA4f.3VkRNjGvu.rB7JNpJUk8v78cXDNPgoStS9TKXQSi', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:41:45'),
(2200538, 'storagemart.joeytecson@gmail.com', '$2y$10$7YLFAl9a1xefs6p8mCHhJeB5XDWBRiCPC5bdjOE7OohzW.IivzdNm', 'EMPLOYEE', 'ACTIVE', 'admin', '2026-03-23 04:42:51'),
(2200539, 'storagemart.shenaamogues@gmail.com', '$2y$10$L1z/zZFDtlS0ywqYm3KWye96yEuw4Py27yZ08txCw20T2Mn8nryaq', 'HEAD', 'ACTIVE', 'Kenneth', '2026-03-23 04:42:56'),
(2200540, 'storagemart.jeraldarandilla@gmail.com', '$2y$10$xWEaqZqBR1wgOF.BytSWPOQRBwffsQrLYQWWKrzEBbuhvRwFXK/Xa', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:48:51'),
(2200541, 'storagemart.charliesalonga@gmail.com', '$2y$10$NC.ZOPdcPzrQE3YCra5xnO5Ln9Qe0Wxm2hbH60EPSVyQ9V0IfY3.a', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:50:31'),
(2200542, 'storagemart.charliesalonga@gmail.com', '$2y$10$Nf5GKUTo9gTfzTAzGZKHPuICVgkpYSFCGFDKb13lRXtK4H2SeD05y', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:51:54'),
(2200543, 'storagemart.darylmacabus@gmail.com', '$2y$10$TSAur0AbmqLM9azhleFYtOBQ9b7t9MiUcJF3bkMRvJSW3hC61sSXu', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:53:36'),
(2200544, 'storagemart.sherwinsalivio@gmail.com', '$2y$10$9Hmw4GXGgGaR1rMcJ8FCmONO04mgNlDEiT8iBpkG9pYpFuZdn80pO', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:54:54'),
(2200545, 'storagemart.joannekamlon@gmail.com', '$2y$10$RRck5b2g5XyCht/Lxx4AauzpJ4MOi5/ZVA7PA7r2dl4KGYHMydLcC', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:56:06'),
(2200546, 'storagemart.carlossipin@gmail.com', '$2y$10$25tEv805ftPW514OHtEJwOTRsucb9SEPPGu3MBuhFdUUjnFW2/7Ii', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:57:47'),
(2200547, 'storagemart.gwynvillanueva@gmail.com', '$2y$10$ZZI6s8Tn0DUBs9PnU1M2Qe0uqHNjRtQLCPRIFAUFBeYy5hCyiH0mu', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 04:59:16'),
(2200548, 'storagemart.operations@gmail.com', '$2y$10$uS3Joz.Cg2IfHWkYCwTY3uklPSMc1MDVGFaBV7YB1nPHt09iH4fB6', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 05:00:51'),
(2200549, 'storagemart.fionacruz@gmail.com', '$2y$10$0LJ4R8QLkkxehNzF0ctTP.S1ZCCnDf6ldL71SKpojyfTLN3otNowK', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 05:02:20'),
(2200550, 'storagemart.dustinzamora@gmail.com', '$2y$10$bbZ6xXJ5Bd/BNaqkKJTdzunAdZ1ZU8fUh1R6zzOXhVpaJNrh1wTfe', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 05:03:36'),
(2200551, 'storagemart.patrickuy@gmail.com', '$2y$10$BpnDNTfnAT4W2iNEYBQI5.8Dx1S/sbi0WTBTEBK9L72pBngGmK5Au', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:17:23'),
(2200552, 'storagemart.pamelaleogo@gmail.com', '$2y$10$t8dsYa/kHlQCIX.say3olueXXnjJc8h7py63xsT6cdMweiNYtDf.W', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:18:33'),
(2200553, 'storagemart.arisdeguzman@gmail.com', '$2y$10$2IsDfxP.t5/n6OInbD8o2ewWXtsqJq3F.A3hDG8J.g2PpKv4696QC', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:20:10'),
(2200554, 'storagemart.annabarretto@gmail.com', '$2y$10$WRTCy5U9907DiWBqpMXnw.U0i0gKjXnz5ZbDM.y0QffBRz15erXje', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:21:32'),
(2200555, 'storagemart.shaireesolito@gmail.com', '$2y$10$HdCu6fI/7phkxcER5.BO1ex0rNA26vHYjuVM8hsQ8sRTmEm0qyagi', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:23:07'),
(2200556, 'storagemart.paultioxon@gmail.com', '$2y$10$psICvX9OFXhjxPDLeOsS5.6NRq9WVAIq/mKvdKQgKxx6Jz03jPHR6', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:24:18'),
(2200557, 'storagemart.mariahbarra@gmail.com', '$2y$10$oXUuFSiUBOk863qEyOWb8.a0qNx6uvrXgEkKBEM0om1fTg1ZK/PTa', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:25:46'),
(2200558, 'storagemart.fonzurrutia@gmail.com', '$2y$10$6WJkrLQYV8KVExN7h3Gnl.11sNQf8ojJBHLB9uR4HY21GP0sYTrCa', 'IT', 'ACTIVE', 'Kenneth', '2026-03-23 20:27:38'),
(2200559, 'storagemart.charlesbacani@gmail.com', '$2y$10$g7beAEHPVxzJBPaYhm.hQOct9TeWV19B2XnWLHG//qWE1iQ2doU/.', 'IT', 'ACTIVE', 'Kenneth', '2026-03-23 20:28:56'),
(2200560, 'storagemart.neomiherrera@gmail.com', '$2y$10$ZjS60Bm6ddD8ukYWg3M8TurskeADWfEzWy5OXuuJuhESNVHVN1UZG', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:30:15'),
(2200561, 'storagemart.kennethdebide@gmail.com', '$2y$10$FYh0K8oZZVz.hDofTr97yuX/FcfRXr7ImchRskvBJKemHbdmV.u5i', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:31:39'),
(2200562, 'storagemart.alvinsantos@gmail.com', '$2y$10$L.Suw8KIkrHArw2zkUsP6e2pPOS7HAKnMV5m0W1yi3FHp5/YkqGZy', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:34:02'),
(2200563, 'storagemart.louiemagbanua@gmail.com', '$2y$10$.w6BcyF3v507Egr9ePEzjOirDzzWsiFK1uwvOs0MSjlfBKr5qu2FG', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:35:30'),
(2200564, 'storagemart.dencelebon@gmail.com', '$2y$10$0hbiQnaL7Py5ZiJ8ilrQM.Qd6qpHLtc5lkVVp7ySw9xVVihEsHmBq', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:41:10'),
(2200565, 'storagemart.ariestenolete@gmail.com', '$2y$10$sRexDIJN8i1v/RUTHKsYZOUzOWig1D8k8Vo4V4PmBa43WqeUobIZa', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:42:21'),
(2200566, 'storagemart.azariahemano@gmail.com', '$2y$10$mtiXu5qjp66IUQqX.2SDWOJKsDzaMG/LoXWtAWE3hdY8WfDCv5saS', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:43:35'),
(2200567, 'storagemart.junesoriano@gmail.com', '$2y$10$5Cb0jd4bgHtfft319Xio2uSEJad.W9i3oJQKT5Prxl9R1yuSLP2Uq', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:44:46'),
(2200568, 'storagemart.katrinneespiritu@gmail.com', '$2y$10$4AjVz7cI8BoZj7OIJrrf8uF5jAILUfPhFm6SrwTFoADb3W8lgw1se', 'HEAD', 'ACTIVE', 'Kenneth', '2026-03-23 20:46:22'),
(2200569, 'storagemart.alexanderdulay@gmail.com', '$2y$10$YdY1jo9eamFv7j0bNXu39O4rxrHt0FAnm0TOPY61SYN8rQVmu20Ee', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:47:28'),
(2200570, 'storagemart.carmelatatel@gmail.com', '$2y$10$/vLIa8Pl6If7p2LUIIvhv.atSnHAJOFFLDyutme15gD5/.ZzKSXOG', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:48:35'),
(2200571, 'storagemart.albinoclavel@gmail.com', '$2y$10$h/zLjnfAmR.CQt8DpULeneWfFGxXGJGYZzrOnCD7sFPhZ7D4nVfUC', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 20:50:24'),
(2200572, 'storagemart.marielmoloboco@gmail.com', '$2y$10$bFivsCV54GQqxGeODWCm8eEWkr/VnJefuPIWjvEQ9ljWzdyQzfPsG', 'EMPLOYEE', 'ACTIVE', 'Kenneth', '2026-03-23 21:26:04');

-- --------------------------------------------------------

--
-- Table structure for table `tblassets_assignment`
--

CREATE TABLE `tblassets_assignment` (
  `assignment_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `assignedTo` varchar(150) NOT NULL,
  `dateIssued` varchar(50) NOT NULL,
  `transferDetails` varchar(200) NOT NULL,
  `transferCount` varchar(50) NOT NULL,
  `dateReturned` varchar(50) DEFAULT NULL,
  `datecreated` varchar(50) NOT NULL,
  `createdby` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblassets_assignment`
--

INSERT INTO `tblassets_assignment` (`assignment_id`, `inventory_id`, `employee_id`, `assignedTo`, `dateIssued`, `transferDetails`, `transferCount`, `dateReturned`, `datecreated`, `createdby`) VALUES
(95, 25, 350150184, 'Santos, Alvin Abad', '2026-03-26', 'ddd', '001', NULL, '2026-03-26 01:47:37', '2200426');

-- --------------------------------------------------------

--
-- Table structure for table `tblassets_category`
--

CREATE TABLE `tblassets_category` (
  `category_id` int(11) NOT NULL,
  `ic_code` varchar(100) NOT NULL,
  `categoryName` varchar(100) NOT NULL,
  `createdby` varchar(50) NOT NULL,
  `datecreated` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblassets_category`
--

INSERT INTO `tblassets_category` (`category_id`, `ic_code`, `categoryName`, `createdby`, `datecreated`) VALUES
(16, 'OE', 'Office Equipment', 'josh', '2025-10-27 18:31:42'),
(17, 'FF', 'Fixture & Furniture', 'josh', '2025-10-27 19:49:19'),
(18, 'OA', 'Other Assets', 'josh', '2025-10-27 19:50:44'),
(19, 'CM', 'Communication', 'josh', '2025-10-27 19:51:38'),
(20, 'IS', 'IT Assets', 'josh', '2025-10-27 19:52:23'),
(21, 'CA', 'Company Attire', 'josh', '2025-10-27 19:52:49'),
(22, 'test', 'test', '2200426', '2025-12-11 16:12:04'),
(23, 'OE3', 'Office EquipmentAaa', '2200426', '2026-03-11 22:00:26');

-- --------------------------------------------------------

--
-- Table structure for table `tblassets_directory`
--

CREATE TABLE `tblassets_directory` (
  `item_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `ic_code` varchar(100) NOT NULL,
  `itemNumber` varchar(20) NOT NULL,
  `itemInfo` varchar(300) NOT NULL,
  `itemModel` varchar(100) NOT NULL,
  `serialNumber` varchar(50) NOT NULL,
  `itemCount` int(11) NOT NULL,
  `status` enum('ACTIVE','DISPOSED','LOST') NOT NULL,
  `year_purchased` year(4) NOT NULL,
  `datecreated` varchar(50) NOT NULL,
  `createdby` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tblassets_group`
--

CREATE TABLE `tblassets_group` (
  `group_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `ic_code` varchar(20) NOT NULL,
  `groupName` varchar(150) NOT NULL,
  `description` varchar(150) NOT NULL,
  `datecreated` varchar(20) NOT NULL,
  `createdby` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblassets_group`
--

INSERT INTO `tblassets_group` (`group_id`, `category_id`, `ic_code`, `groupName`, `description`, `datecreated`, `createdby`) VALUES
(6, 16, 'OE', 'Lenovo', 'Laptop', '2025-10-27 18:32:24', 'josh'),
(7, 16, 'OE', 'HP', 'Laptop', '2025-10-29 16:13:19', 'admin'),
(8, 16, 'OE', 'DERE', 'Laptop', '2025-11-07 13:42:49', 'admin'),
(9, 21, '', 'DEREdd', 'daddada', '2026-03-26 01:17:50', '2200426');

-- --------------------------------------------------------

--
-- Table structure for table `tblassets_inventory`
--

CREATE TABLE `tblassets_inventory` (
  `inventory_id` int(11) NOT NULL,
  `assignment_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `group_id` int(11) NOT NULL,
  `serialNumber` varchar(100) NOT NULL,
  `itemInfo` varchar(150) NOT NULL,
  `status` varchar(30) NOT NULL,
  `assetCode` varchar(200) NOT NULL,
  `assetNumber` varchar(200) NOT NULL,
  `year_purchased` varchar(50) NOT NULL,
  `datecreated` varchar(50) NOT NULL,
  `createdby` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblassets_inventory`
--

INSERT INTO `tblassets_inventory` (`inventory_id`, `assignment_id`, `employee_id`, `branch_id`, `group_id`, `serialNumber`, `itemInfo`, `status`, `assetCode`, `assetNumber`, `year_purchased`, `datecreated`, `createdby`) VALUES
(25, 95, 350150184, 20, 8, '123', 'dada', 'ASSIGNED', '1', 'OE-15001-RVG001', '2015', '2026-03-26 01:46:55', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `tblbranch`
--

CREATE TABLE `tblbranch` (
  `branch_id` int(11) NOT NULL,
  `branchCode` varchar(20) NOT NULL,
  `branchName` varchar(100) NOT NULL,
  `branchAddress` varchar(150) NOT NULL,
  `datecreated` varchar(50) NOT NULL,
  `createdby` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblbranch`
--

INSERT INTO `tblbranch` (`branch_id`, `branchCode`, `branchName`, `branchAddress`, `datecreated`, `createdby`) VALUES
(1, 'HO', 'Head Office', '3112 Iran Street, Makati City, 1213 Metro Manila', '10/27/2025', 'admin'),
(5, 'DAR', 'Don Roces', '127 Don A. Roces Ave, Diliman, Quezon City, 1103 Metro Manila', '2025-10-29 09:53:20', 'test'),
(6, 'SCT', 'Sucat', 'Dr Arcadio Santos Ave, Parañaque, 1700 Metro Manila', '2025-10-29 09:53:54', 'test'),
(7, 'QAB', 'Banawe', '388 Quezon Ave, Quezon City, 1113 Metro Manila', '2025-10-29 09:54:38', 'test'),
(8, 'STL', 'Santolan', 'Little Bagui, 298 Col. ?????????????? San Juan City, 1500 Metro Manila', '2025-10-29 09:55:42', 'test'),
(9, 'PSG', 'Pasig', 'MP Building, Jose C.Cruz, Pasig, 1604 Metro Manila', '2025-10-29 09:56:07', 'test'),
(10, 'BKL', 'Bangkal', '19 Epifanio de los Santos Ave, Makati City, Metro Manila', '2025-10-29 09:56:54', 'test'),
(11, 'QAD', 'Delta', '1231 Quezon Avenue, corner Jose Abad Santos, Quezon City, 1104', '2025-10-29 09:57:52', 'test'),
(12, 'BND', 'Binondo', '407 Dasmarinas, Cor Burke St, Binondo, Manila, 1006 Metro Manila', '2025-10-29 09:58:27', 'test'),
(13, 'IRN', 'Eran', '3112 Iran Street, Makati City, 1213 Metro Manila', '2025-10-29 09:58:49', 'test'),
(14, 'KTP', 'Katipunan', '311 Katipunan Ave, Quezon City, 1108 Metro Manila', '2025-10-29 09:59:15', 'test'),
(15, 'FVW', 'Fairview', 'Block 63 Lot 12, Brgy, 14 Commonwealth Ave, Quezon City, 1121 Metro Manila', '2025-10-29 09:59:43', 'test'),
(16, 'JBD', 'Jabad', '3F, WNC Building, 15 Jose Abad Santos, San Juan City, 1500 Metro Manila', '2025-10-29 10:00:25', 'test'),
(17, 'YKL', 'Yakal', 'Warehouse C, 7452 Yakal, Village, Makati City, 1203 Metro Manila', '2025-10-29 10:00:54', 'test'),
(18, 'CLC', 'Caloocan', '152 D. Aquino St, Grace Park West, Caloocan, 1406 Metro Manila', '2025-10-29 10:02:30', 'test'),
(20, 'RVG', 'Roving', 'Tristar Building, Iran Street, Makati City, 1213 Metro Manila', '2026-03-23 03:56:10', '2200426'),
(21, '12232', 'Bakal', 'diko saan', '2026-03-26 01:16:09', '2200426');

-- --------------------------------------------------------

--
-- Table structure for table `tblemployee`
--

CREATE TABLE `tblemployee` (
  `employee_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `createdby` varchar(50) NOT NULL,
  `datecreated` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblemployee`
--

INSERT INTO `tblemployee` (`employee_id`, `account_id`, `branch_id`, `lastname`, `firstname`, `middlename`, `department`, `position`, `email`, `createdby`, `datecreated`) VALUES
(1072025, 2200458, 1, 'Dador', 'Kenneth', 'Clores', 'IT', 'IT Supervisor', 'storagemart.it@gmail.com', 'admin', '2026-03-23 01:10:01'),
(2026221, 2200516, 1, 'Lopez', 'Xyza Yra Zindel', 'Gallegos', 'HRMD', 'HR Specialist', 'storagemart.xyzalopez@gmail.com', 'Kenneth', '2026-03-23 04:16:39'),
(2026222, 2200514, 1, 'Arias', 'Ralph Lauren', 'Mercado', 'Sales', 'Sales Associate', 'storagemart.laurenarias@gmail.com', 'Kenneth', '2026-03-23 04:15:32'),
(2026224, 2200513, 1, 'Domingo', 'Francis Jim Miguel', 'Alba', 'Marketing', 'Marketing Assistant', 'storagemart.francisdomingo@gmail.com', 'Kenneth', '2026-03-23 04:14:22'),
(2026261, 2200509, 1, 'Banzuela', 'Kryzle Ashley', 'Miranda', 'HRMD', 'HR Specialist', 'storagemart.kryzlebanzuela@gmail.com', 'Kenneth', '2026-03-23 04:10:19'),
(2026262, 2200510, 1, 'Braceros', 'Geraldine', 'Rosquita', 'Compliance', 'Compliance Associate', 'storagemart.geraldinebraceros@gmail.com', 'Kenneth', '2026-03-23 04:11:40'),
(2026291, 2200520, 1, 'Prochina', 'Meryl', 'Lequin', 'Operations', 'Permit and Licensing Associate', 'storagemart.merylprochina@gmail.com', 'Kenneth', '2026-03-23 04:22:13'),
(2026292, 2200511, 1, 'Ronquillo', 'Mark Lester', 'Panganiban', 'HRMD', 'HR Manager', 'storagemart.lesterronquillo@gmail.com', 'Kenneth', '2026-03-23 04:12:42'),
(2200424, 2200425, 1, 'Ricafort', 'Roland Josh', 'Manalo', 'IT', 'Intern', 'rj.ricafort21@nullsto.edu.pl', 'admin', '10/27/2025'),
(2200425, 2200426, 1, 'Howard', 'Sy', '', 'Storage Mart', 'Chief executive officer', 'rj.ricafort21@nullsto.edu.pl', 'josh', '10/27/2025'),
(2500123, 2200454, 16, 'DelaCruz', 'Juan', '', 'Accounting', 'Assistant Accounting', 'rj.ricafort21@nullsto.edu.pl', 'admin', '2025-12-18 08:08:18'),
(2600163, 2200521, 1, 'Magon', 'Micahbel Ashley', 'Flores', 'Purchasing', 'Purchasing Associate', 'storagemart.micahbelmagon@gmail.com', 'Kenneth', '2026-03-23 04:25:09'),
(2600165, 2200522, 1, 'Del Mundo', 'Patric-Ahl', 'N/A', 'Accounting', 'Accounting Assistant', 'storagemart.patricdelmundo@gmail.com', 'Kenneth', '2026-03-23 04:26:36'),
(20251211, 2200551, 7, 'Uy', 'Patrick Gabriel', 'Ng', 'Operations', 'Operations Management Trainee', 'storagemart.patrickuy@gmail.com', 'Kenneth', '2026-03-23 20:17:23'),
(20251212, 2200552, 13, 'Leogo', 'Pamela', 'Sarne', 'Operations', 'Operations Management Trainee', 'storagemart.pamelaleogo@gmail.com', 'Kenneth', '2026-03-23 20:18:33'),
(20261191, 2200518, 9, 'Oriel', 'Elvin', 'Pagsolingan', 'Operations', 'Facility Staff', 'storagemart.elvinoriel@gmail.com', 'Kenneth', '2026-03-23 04:17:46'),
(22001234, 2200455, 14, 'Wigburg', 'Jacinda', '', 'Accounting', 'Head Accounting', 'maedandoy04@gmail.com', 'admin', '2025-12-18 08:09:42'),
(202501071, 2200428, 1, 'Dador', 'Kenneth', '', 'IT', 'IT Support Associate', 'storagemart.it@gmail.com', 'test', '10/28/2025'),
(202502071, 2200538, 8, 'Tecson', 'Joey', 'Villarta', 'Operations', 'Facility Staff', 'storagemart.joeytecson@gmail.com', 'admin', '2026-03-23 04:42:51'),
(202503171, 2200546, 1, 'Sipin', 'Carlos Juan', 'Cullano', 'Digital Marketing', 'Multimedia Artist', 'storagemart.carlossipin@gmail.com', 'Kenneth', '2026-03-23 04:57:47'),
(202503241, 2200547, 1, 'Villanueva', 'Gwyneth Kate', 'Forio', 'Accounting', 'Accounting Manager', 'storagemart.gwynvillanueva@gmail.com', 'Kenneth', '2026-03-23 04:59:16'),
(202503251, 2200548, 6, 'Urubio', 'Josel Anne', 'Estrada', 'Operations', 'Facility Officer', 'storagemart.operations@gmail.com', 'Kenneth', '2026-03-23 05:00:51'),
(202504031, 2200549, 1, 'Cruz', 'Fiona Angeline', 'Villamor', 'Marketing', 'Marketing Officer', 'storagemart.fionacruz@gmail.com', 'Kenneth', '2026-03-23 05:02:20'),
(202504141, 2200550, 15, 'Zamora', 'Dustin Clifford', 'Medina', 'Operations', 'Facility Staff', 'storagemart.dustinzamora@gmail.com', 'Kenneth', '2026-03-23 05:03:36'),
(202504282, 2200563, 6, 'Magbanua', 'Louie Aji', 'Nalaunan', 'Operations', 'Facility Staff', 'storagemart.louiemagbanua@gmail.com', 'Kenneth', '2026-03-23 20:35:30'),
(202506031, 2200572, 9, 'Moloboco', 'Mariel', 'Casero', 'Operations', 'Operations Management Trainee', 'storagemart.marielmoloboco@gmail.com', 'Kenneth', '2026-03-23 21:26:04'),
(202507213, 2200564, 10, 'Ebon', 'Dencel Aster', 'N/A', 'Operations', 'Facility Officer', 'storagemart.dencelebon@gmail.com', 'Kenneth', '2026-03-23 20:41:10'),
(202507214, 2200565, 11, 'Tenolete', 'Aries', 'Baylon', 'Operations', 'Facility Staff', 'storagemart.ariestenolete@gmail.com', 'Kenneth', '2026-03-23 20:42:21'),
(202507281, 2200566, 15, 'Emano', 'Azariah Ericka', 'Echague', 'Operations', 'Facility Officer', 'storagemart.azariahemano@gmail.com', 'Kenneth', '2026-03-23 20:43:35'),
(202508111, 2200567, 18, 'Soriano', 'June Gerard', 'Torralba', 'Operations', 'Operations Management Trainee', 'storagemart.junegeraldsoriano@gmail.com', 'Kenneth', '2026-03-23 20:44:46'),
(202508181, 2200568, 13, 'Espiritu', 'Katrinne Louise', 'Magayaga', 'Operations', 'Head of Operatios', 'storagemart.katrinneespiritu@gmail.com', 'Kenneth', '2026-03-23 20:46:22'),
(202509151, 2200569, 7, 'Dulay', 'Alexander', 'Valenzuela', 'Operations', 'Facility Staff', 'storagemart.alexanderdulay@gmail.com', 'Kenneth', '2026-03-23 20:47:28'),
(202509153, 2200570, 1, 'Tatel', 'Carmela', 'Cabatbat', 'Accounting', 'Accounting Assistant', 'storagemart.carmelatatel@gmail.com', 'Kenneth', '2026-03-23 20:48:35'),
(202510061, 2200571, 18, 'Clavel Jr', 'Albino', 'Reyes', 'Operations', 'Facility Staff', 'storagemart.albinoclavel@gmail.com', 'Kenneth', '2026-03-23 20:50:24'),
(202510062, 2200560, 7, 'Herrera', 'Neomi Grace', 'Canoza', 'Operations', 'Operations Management Trainee', 'storagemart.neomiherrera@gmail.com', 'Kenneth', '2026-03-23 20:30:15'),
(202510201, 2200559, 1, 'Bacani', 'Charles Darwin', 'Lorenzo', 'IT', 'IT Tech Associate', 'storagemart.charlesbacani@gmail.com', 'Kenneth', '2026-03-23 20:28:56'),
(202510202, 2200558, 1, 'Urrutia', 'Fonzy Kyle', 'Lara', 'IT', 'IT Tech Associate', 'storagemart.fonzurrutia@gmail.com', 'Kenneth', '2026-03-23 20:27:38'),
(202510241, 2200557, 15, 'Barra', 'Mariah Gabrielle Charm', 'N/A', 'Operations', 'Operations Management Trainee', 'storagemart.mariahbarra@gmail.com', 'Kenneth', '2026-03-23 20:25:46'),
(202510272, 2200556, 5, 'Tioxon', 'Paul Andrew', 'Lara', 'Operations', 'Facility Staff', 'storagemart.paultioxon@gmail.com', 'Kenneth', '2026-03-23 20:24:18'),
(202511102, 2200555, 13, 'Solito', 'Shairee Savier', 'Faura', 'Operations', 'Facility Staff', 'storagemart.shaireesolito@gmail.com', 'Kenneth', '2026-03-23 20:23:07'),
(202511171, 2200553, 9, 'De Guzman', 'Aris Robin', 'Moralde', 'Operations', 'Operations Management Trainee', 'storagemart.arisdeguzman@gmail.com', 'Kenneth', '2026-03-23 20:20:10'),
(202511172, 2200554, 1, 'Barretto', 'Anna Gabriella', 'Reginaldo', 'Marketing', 'Marketing Officer', 'storagemart.annabarretto@gmail.com', 'Kenneth', '2026-03-23 20:21:32'),
(202512091, 2200525, 1, 'Rayon', 'Girlie', 'Diaz', 'Purchasing', 'Payroll and Benefits Officer', 'storagemart.girlierayon@gmail.com', 'Kenneth', '2026-03-23 04:29:34'),
(202512221, 2200523, 1, 'Juarez', 'Amelina', 'Licuan', 'Accounting', 'Senior Accountant', 'storagemart.amelinajuarez@gmail.com', 'Kenneth', '2026-03-23 04:27:50'),
(202512222, 2200519, 11, 'Tolentino', 'Mark Angelo', 'Gubatan', 'Operations', 'Facility Staff', 'storagemart.marktolentino@gmail.com', 'Kenneth', '2026-03-23 04:19:07'),
(230004935, 2200540, 20, 'Arandilla', 'Jerald', 'Orbita', 'Accounting', 'Audit Manager', 'storagemart.jeraldarandilla@gmail.com', 'Kenneth', '2026-03-23 04:48:51'),
(230004943, 2200497, 6, 'Mangaban', 'Dan Paolo', 'Alejandrino', 'Operations', 'Facility Officer', 'storagemart.danmangaban@gmail.com', 'Kenneth', '2026-03-23 03:52:19'),
(230005052, 2200541, 20, 'Salonga', 'Charlie', 'Munoz', 'Operations', 'Packing Manager', 'storagemart.charliesalonga@gmail.com', 'Kenneth', '2026-03-23 04:50:31'),
(230005087, 2200542, 20, 'Alejo', 'Vivian', 'Gomez', 'Operations', 'Construction Manager', 'storagemart.vivianalejo@gmail.com', 'Kenneth', '2026-03-23 04:51:54'),
(230005109, 2200498, 1, 'Abueva', 'Ann Mercy', 'Faura', 'Sales', 'Head of Sales', 'storagemart.annabueva@gmail.com', 'Kenneth', '2026-03-23 03:55:43'),
(230005133, 2200499, 17, 'Tangunan', 'Julie An', 'Sardan', 'Operations', 'Area Operations Manager', 'storagemart.julieantangunan@gmail.com', 'Kenneth', '2026-03-23 03:58:13'),
(230005141, 2200501, 1, 'Bernal', 'Samuel', 'Lachica', 'Sales', 'Sales Associate', 'storagemart.sambernal@gmail.com', 'Kenneth', '2026-03-23 03:59:37'),
(230005206, 2200544, 20, 'Salivio', 'Sherwin', 'Juniller', 'Operations', 'Packing Lead', 'storagemart.sherwinsalivio@gmail.com', 'Kenneth', '2026-03-23 04:54:54'),
(230005265, 2200502, 1, 'Bordon', 'Charrise Ann', 'Lopez', 'Sales', 'Sales Associate', 'storagemart.charrisebordon@gmail.com', 'Kenneth', '2026-03-23 04:01:09'),
(230005273, 2200543, 20, 'Macabus', 'Daryl', 'N/A', 'Operations', 'Construction/Maintenance Staff', 'storagemart.darylmacabus@gmail.com', 'Kenneth', '2026-03-23 04:53:36'),
(230005338, 2200503, 14, 'Jose', 'John Karl', 'Santos', 'Operations', 'Area Operations Manager', 'storagemart.karljose@gmail.com', 'Kenneth', '2026-03-23 04:02:39'),
(230005486, 2200504, 1, 'Madla', 'Rose Anne', 'Solas', 'HRMD', 'Head of HRMD', 'storagemart.rosemadla@gmail.com', 'Kenneth', '2026-03-23 04:03:50'),
(230005575, 2200505, 12, 'Ramos', 'Christian', 'Roque', 'Operations', 'Facility Manager', 'storagemart.christianramos@gmail.com', 'Kenneth', '2026-03-23 04:05:11'),
(230005613, 2200506, 6, 'Ramos Jr.', 'Antonio', 'Lucero', 'Operations', 'Facility Staff', 'storagemart.antonioramosjr@gmail.com', 'Kenneth', '2026-03-23 04:06:33'),
(230005621, 2200507, 17, 'Nuqui', 'Sylene Anne', 'Sapalaran', 'Operations', 'Facility Manager', 'storagemart.sylenenuqui@gmail.com', 'Kenneth', '2026-03-23 04:07:43'),
(230005656, 2200508, 6, 'Lingues', 'Mary Jane', 'Ibañez', 'Operations', 'Valet Officer', 'storagemart.maryjanelingues@gmail.com', 'Kenneth', '2026-03-23 04:08:49'),
(230005826, 2200539, 1, 'Amogues', 'Shena Mae', 'Tagra', 'Purchasing', 'Head of Purchasing', 'storagemart.shenaamogues@gmail.com', 'Kenneth', '2026-03-23 04:42:56'),
(230005958, 2200545, 1, 'Kamlon', 'Joanne', 'Tawasil', 'Sales', 'Sales Associate', 'storagemart.joannekamlon@gmail.com', 'Kenneth', '2026-03-23 04:56:06'),
(230005966, 2200537, 17, 'Prudencio', 'Lea', 'Asuncion', 'Operations', 'Facility Staff', 'storagemart.leaprudencio@gmail.com', 'Kenneth', '2026-03-23 04:41:45'),
(230006008, 2200535, 7, 'Nicolas', 'Joshua Albert', 'N/A', 'Operations', 'Facility Staff', 'storagemart.joshuanicolas@gmail.com', 'Kenneth', '2026-03-23 04:40:43'),
(230006016, 2200533, 1, 'Gonzales', 'Joana Marie', 'Naval', 'Accounting', 'Senior Accountant', 'storagemart.joanagonzales@gmail.com', 'Kenneth', '2026-03-23 04:39:39'),
(230006032, 2200532, 1, 'Vitug', 'Verielle Jay', 'Baguio', 'Accounting', 'Accounting Assistant-Compliance', 'storagemart.veriellevitug@gmail.com', 'Kenneth', '2026-03-23 04:38:36'),
(230006059, 2200530, 13, 'Revuelta', 'Jermalyn', 'Hajie', 'Operations', 'Area Operations Manager', 'storagemart.jermalynrevuelta@gmail.com', 'Kenneth', '2026-03-23 04:37:23'),
(230006067, 2200528, 1, 'Romero', 'Hannah Gee', 'N/A', 'Accounting', 'Accounting Assistant', 'storagemart.hannahromero@gmail.com', 'Kenneth', '2026-03-23 04:36:12'),
(230006075, 2200527, 15, 'Roxas', 'Reynald Christopher', 'Nemenzo', 'Operations', 'Facility Staff', 'storagemart.reynaldroxas@gmail.com', 'Kenneth', '2026-03-23 04:34:48'),
(350081936, 2200517, 5, 'Tamayo', 'Patricia', 'Doronila', 'Operations', 'Facility Manager', 'storagemart.patriciatamayo@gmail.com', 'admin', '2026-03-23 04:17:38'),
(350120040, 2200561, 10, 'Debide', 'Kenneth', 'Ramos', 'Operations', 'Facility Staff', 'storagemart.kennethdebide@gmail.com', 'Kenneth', '2026-03-23 20:31:39'),
(350136175, 2200500, 1, 'Obnamia', 'Mariel', 'Vibal', 'Accounting', 'Accounting Assistant', 'storagemart.marielobnamia@gmail.com', 'admin', '2026-03-23 03:58:36'),
(350146257, 2200512, 13, 'Prado Jr.', 'Alfer', 'Berganos', 'Operations', 'Facility Staff', 'storagemart.alferpradojr@gmail.com', 'admin', '2026-03-23 04:14:05'),
(350150170, 2200515, 1, 'Reyes', 'Johanna May', 'Ordoñez', 'Purchasing', 'Purchasing Associate', 'storagemart.johannareyes@gmail.com', 'admin', '2026-03-23 04:15:41'),
(350150184, 2200562, 20, 'Santos', 'Alvin', 'Abad', 'Operations', 'Technical Maintenance Staff', 'storagemart.alvinsantos@gmail.com', 'Kenneth', '2026-03-23 20:34:02'),
(350165478, 2200529, 14, 'Pendon', 'Reynald', 'N/A', 'Operations', 'Facility Staff', 'storagemart.reynaldpendon@gmail.com', 'admin', '2026-03-23 04:36:38'),
(350165484, 2200524, 13, 'Bosita', 'Maevelyn', 'Labog', 'Operations', 'Facility Manager', 'storagemart.maevelynbosita@gmail.com', 'admin', '2026-03-23 04:29:24'),
(350165486, 2200526, 8, 'Lasquite', 'Kyla', 'Acosta', 'Operations', 'Facility Manager', 'storagemart.kylalasquite@gmail.com', 'admin', '2026-03-23 04:33:30'),
(350174677, 2200531, 12, 'Ignacio', 'Jusefher', 'Badal', 'Operations', 'Facility Staff', 'storagemart.jusefherignacio@gmail.com', 'admin', '2026-03-23 04:38:24'),
(350174681, 2200534, 11, 'Usalla', 'Peter', 'Descaya', 'Operations', 'Facility Officer', 'storagemart.peterusalla@gmail.com', 'admin', '2026-03-23 04:39:56'),
(350188592, 2200536, 1, 'Panday', 'Erika', 'Sayson', 'Operations', 'Sales Management Trainee', 'storagemart.erikapanday@gmail.com', 'admin', '2026-03-23 04:41:25');

-- --------------------------------------------------------

--
-- Table structure for table `tbllogs`
--

CREATE TABLE `tbllogs` (
  `datelog` varchar(20) NOT NULL,
  `timelog` varchar(20) NOT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(50) NOT NULL,
  `ID` varchar(20) NOT NULL,
  `performedby` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbllogs`
--

INSERT INTO `tbllogs` (`datelog`, `timelog`, `action`, `module`, `ID`, `performedby`) VALUES
('2025-12-18', '08:06:23', 'Transferred asset OE', 'Asset Inventory', '16', '2200426'),
('2025-12-18', '08:06:23', 'Transfer Asset', 'Asset Inventory', '2200424', 'admin'),
('2025-12-18', '08:08:18', 'Create Account', 'Employee Management', '2500123', 'admin'),
('2025-12-18', '08:09:42', 'Create Account', 'Employee Management', '22001234', 'admin'),
('2026-01-05', '12:35:40', 'Create', 'Ticket Management', '69', 'smiran.rolandjosh'),
('2026-01-05', '12:48:39', 'Transferred asset OE-24003 to Wigburg, Jacinda  (KTP)', 'Asset Inventory', '11', '2200426'),
('2026-01-05', '12:48:39', 'Transfer Asset', 'Asset Inventory', '22001234', 'admin'),
('2026-01-05', '13:04:15', 'Transferred asset OE-24004 to Wigburg, Jacinda  (KTP)', 'Asset Inventory', '12', '2200426'),
('2026-01-05', '13:04:15', 'Transfer Asset', 'Asset Inventory', '22001234', 'admin'),
('2026-01-05', '13:25:07', 'Update Item', 'Item Asset', 'Inventory 11', 'admin'),
('2026-01-05', '13:48:30', 'Update Item', 'Item Asset', 'Inventory 11', 'admin'),
('2026-01-05', '13:48:55', 'Transferred asset OE-25002 to DelaCruz, Juan  (JBD)', 'Asset Inventory', '10', '2200426'),
('2026-01-05', '13:48:55', 'Transfer Asset', 'Asset Inventory', '2500123', 'admin'),
('2026-01-08', '05:30:21', 'Create', 'Ticket Management', '70', 'smiran.rolandjosh'),
('2026-01-08', '05:40:25', 'Approve & Assign', 'Ticket Management', '69', 'admin'),
('2026-01-08', '05:41:15', 'Approve & Assign', 'Ticket Management', '70', 'admin'),
('2026-01-22', '20:34:07', 'Create', 'Ticket Management', '71', 'smiran.rolandjosh'),
('2026-01-22', '20:34:24', 'Approve & Assign', 'Ticket Management', '71', 'admin'),
('2026-01-22', '20:38:07', 'Create', 'Ticket Management', '72', 'smiran.employee'),
('2026-01-22', '20:38:27', 'Approve & Assign', 'Ticket Management', '72', 'admin'),
('2025-12-18', '08:06:23', 'Transferred asset OE', 'Asset Inventory', '16', '2200426'),
('2025-12-18', '08:06:23', 'Transfer Asset', 'Asset Inventory', '2200424', 'admin'),
('2025-12-18', '08:08:18', 'Create Account', 'Employee Management', '2500123', 'admin'),
('2025-12-18', '08:09:42', 'Create Account', 'Employee Management', '22001234', 'admin'),
('2026-01-05', '12:35:40', 'Create', 'Ticket Management', '69', 'smiran.rolandjosh'),
('2026-01-05', '12:48:39', 'Transferred asset OE-24003 to Wigburg, Jacinda  (KTP)', 'Asset Inventory', '11', '2200426'),
('2026-01-05', '12:48:39', 'Transfer Asset', 'Asset Inventory', '22001234', 'admin'),
('2026-01-05', '13:04:15', 'Transferred asset OE-24004 to Wigburg, Jacinda  (KTP)', 'Asset Inventory', '12', '2200426'),
('2026-01-05', '13:04:15', 'Transfer Asset', 'Asset Inventory', '22001234', 'admin'),
('2026-01-05', '13:25:07', 'Update Item', 'Item Asset', 'Inventory 11', 'admin'),
('2026-01-05', '13:48:30', 'Update Item', 'Item Asset', 'Inventory 11', 'admin'),
('2026-01-05', '13:48:55', 'Transferred asset OE-25002 to DelaCruz, Juan  (JBD)', 'Asset Inventory', '10', '2200426'),
('2026-01-05', '13:48:55', 'Transfer Asset', 'Asset Inventory', '2500123', 'admin'),
('2026-01-08', '05:30:21', 'Create', 'Ticket Management', '70', 'smiran.rolandjosh'),
('2026-01-08', '05:40:25', 'Approve & Assign', 'Ticket Management', '69', 'admin'),
('2026-01-08', '05:41:15', 'Approve & Assign', 'Ticket Management', '70', 'admin'),
('2026-01-22', '20:34:07', 'Create', 'Ticket Management', '71', 'smiran.rolandjosh'),
('2026-01-22', '20:34:24', 'Approve & Assign', 'Ticket Management', '71', 'admin'),
('2026-01-22', '20:38:07', 'Create', 'Ticket Management', '72', 'smiran.employee'),
('2026-01-22', '20:38:27', 'Approve & Assign', 'Ticket Management', '72', 'admin'),
('2026-01-22', '20:23:02', 'Create', 'Ticket Management', '73', 'smiran.rolandjosh'),
('2026-01-22', '20:23:31', 'Approve & Assign', 'Ticket Management', '73', 'admin'),
('2026-01-23', '04:01:53', 'Add Asset', 'Asset Inventory', 'Asset ID: 24', 'admin'),
('2026-01-23', '04:02:21', 'Transferred asset OE-24005 to Ricafort, Roland Josh Manalo (HO)', 'Asset Inventory', '24', '2200426'),
('2026-01-23', '04:02:21', 'Transfer Asset', 'Asset Inventory', '2200424', 'admin'),
('2026-03-02', '01:21:27', 'Create Account', 'Employee Management', '2020124312', 'admin'),
('2026-03-02', '01:27:28', 'Update Item', 'Item Asset', 'Inventory 11', 'admin'),
('2026-03-02', '02:21:23', 'Update Item', 'Item Asset', 'Inventory 16', 'admin'),
('2026-03-10', '04:01:38', 'Create Account', 'Employee Management', '202510202', 'admin'),
('2026-03-11', '21:59:35', 'Add Branch', 'Branch Management', 'Hi', 'admin'),
('2026-03-11', '22:00:26', 'Add Category', 'Category Management', 'Office EquipmentAaa', 'admin'),
('2026-03-12', '21:50:20', 'Create', 'Ticket Management', '83', 'smiran.rolandjosh'),
('2026-03-12', '22:53:24', 'Create', 'Ticket Management', '84', 'smiran.head'),
('2026-03-13', '02:37:43AM', 'Reassigned Ticket', 'Ticket Management', '84', 'admin'),
('2026-03-13', '02:59:08AM', 'Reassigned Ticket', 'Ticket Management', '84', 'admin'),
('2026-03-13', '03:02:26AM', 'Reassigned Ticket', 'Ticket Management', '84', 'admin'),
('2026-03-13', '03:25:18', 'Create', 'Ticket Management', '85', 'smiran.head'),
('2026-03-13', '03:25:35AM', 'Reassigned Ticket', 'Ticket Management', '85', 'admin'),
('2026-03-23', '00:58:59', 'Deleted Account', 'Account Management', '2200457', 'admin'),
('2026-03-23', '00:59:03', 'Deleted Account', 'Account Management', '2200456', 'admin'),
('2026-03-23', '01:10:01', 'Create Account', 'Employee Management', '1072025', 'admin'),
('2026-03-23', '02:02:44', 'Create Account', 'Employee Management', '3152024', 'admin'),
('2026-03-23', '02:07:50', 'Create Account', 'Employee Management', '9132017', 'Kenneth'),
('2026-03-23', '02:11:46', 'Create Account', 'Employee Management', '11072016', 'Kenneth'),
('2026-03-23', '02:15:25', 'Create Account', 'Employee Management', '9132027', 'Kenneth'),
('2026-03-23', '02:20:35', 'Create Account', 'Employee Management', '1132020', 'Kenneth'),
('2026-03-23', '02:22:17', 'Create Account', 'Employee Management', '3062021', 'Kenneth'),
('2026-03-23', '02:23:10', 'Create Account', 'Employee Management', '5032024', 'admin'),
('2026-03-23', '02:24:21', 'Create Account', 'Employee Management', '5062021', 'Kenneth'),
('2026-03-23', '02:26:14', 'Create Account', 'Employee Management', '2212022', 'Kenneth'),
('2026-03-23', '02:29:21', 'Create Account', 'Employee Management', '6062022', 'Kenneth'),
('2026-03-23', '02:31:41', 'Create Account', 'Employee Management', '9102022', 'Kenneth'),
('2026-03-23', '02:33:03', 'Create Account', 'Employee Management', '6242024', 'admin'),
('2026-03-23', '02:33:37', 'Create Account', 'Employee Management', '9242022', 'Kenneth'),
('2026-03-23', '02:36:21', 'Create Account', 'Employee Management', '7102024', 'admin'),
('2026-03-23', '02:38:43', 'Create Account', 'Employee Management', '7292024', 'admin'),
('2026-03-23', '02:39:37', 'Create Account', 'Employee Management', '10082022', 'Kenneth'),
('2026-03-23', '02:41:15', 'Create Account', 'Employee Management', '12092022', 'Kenneth'),
('2026-03-23', '02:42:42', 'Create Account', 'Employee Management', '3212023', 'Kenneth'),
('2026-03-23', '02:44:39', 'Create Account', 'Employee Management', '3282023', 'Kenneth'),
('2026-03-23', '02:46:17', 'Create Account', 'Employee Management', '5022023', 'Kenneth'),
('2026-03-23', '02:48:13', 'Create Account', 'Employee Management', '5042023', 'Kenneth'),
('2026-03-23', '02:50:31', 'Create Account', 'Employee Management', '5222023', 'Kenneth'),
('2026-03-23', '02:52:20', 'Create Account', 'Employee Management', '5272023', 'Kenneth'),
('2026-03-23', '02:54:30', 'Create Account', 'Employee Management', '6052023', 'Kenneth'),
('2026-03-23', '02:56:26', 'Create Account', 'Employee Management', '6072023', 'Kenneth'),
('2026-03-23', '02:59:00', 'Create Account', 'Employee Management', '8212024', 'admin'),
('2026-03-23', '03:11:54', 'Create Account', 'Employee Management', '12222025', 'Kenneth'),
('2026-03-23', '03:13:57', 'Create Account', 'Employee Management', '1192026', 'Kenneth'),
('2026-03-23', '03:16:15', 'Create Account', 'Employee Management', '2022026', 'Kenneth'),
('2026-03-23', '03:32:41', 'Create Account', 'Employee Management', '20260202', 'Kenneth'),
('2026-03-23', '03:36:57', 'Create Account', 'Employee Management', '2062026', 'Kenneth'),
('2026-03-23', '03:40:51', 'Create Account', 'Employee Management', '2092026', 'Kenneth'),
('2026-03-23', '03:52:19', 'Create Account', 'Employee Management', '230004943', 'Kenneth'),
('2026-03-23', '03:55:43', 'Create Account', 'Employee Management', '230005109', 'Kenneth'),
('2026-03-23', '03:56:10', 'Add Branch', 'Branch Management', 'Roving', 'admin'),
('2026-03-23', '03:58:13', 'Create Account', 'Employee Management', '230005133', 'Kenneth'),
('2026-03-23', '03:58:36', 'Create Account', 'Employee Management', '350136175', 'admin'),
('2026-03-23', '03:59:37', 'Create Account', 'Employee Management', '230005141', 'Kenneth'),
('2026-03-23', '04:01:09', 'Create Account', 'Employee Management', '230005265', 'Kenneth'),
('2026-03-23', '04:02:39', 'Create Account', 'Employee Management', '230005338', 'Kenneth'),
('2026-03-23', '04:03:50', 'Create Account', 'Employee Management', '230005486', 'Kenneth'),
('2026-03-23', '04:05:11', 'Create Account', 'Employee Management', '230005575', 'Kenneth'),
('2026-03-23', '04:06:33', 'Create Account', 'Employee Management', '230005613', 'Kenneth'),
('2026-03-23', '04:07:43', 'Create Account', 'Employee Management', '230005621', 'Kenneth'),
('2026-03-23', '04:08:49', 'Create Account', 'Employee Management', '230005656', 'Kenneth'),
('2026-03-23', '04:10:19', 'Create Account', 'Employee Management', '2026261', 'Kenneth'),
('2026-03-23', '04:11:40', 'Create Account', 'Employee Management', '2026262', 'Kenneth'),
('2026-03-23', '04:12:42', 'Create Account', 'Employee Management', '2026292', 'Kenneth'),
('2026-03-23', '04:14:05', 'Create Account', 'Employee Management', '350146257', 'admin'),
('2026-03-23', '04:14:22', 'Create Account', 'Employee Management', '2026224', 'Kenneth'),
('2026-03-23', '04:15:32', 'Create Account', 'Employee Management', '2026222', 'Kenneth'),
('2026-03-23', '04:15:41', 'Create Account', 'Employee Management', '350150170', 'admin'),
('2026-03-23', '04:16:39', 'Create Account', 'Employee Management', '2026221', 'Kenneth'),
('2026-03-23', '04:17:38', 'Create Account', 'Employee Management', '350081936', 'admin'),
('2026-03-23', '04:17:46', 'Create Account', 'Employee Management', '20261191', 'Kenneth'),
('2026-03-23', '04:19:07', 'Create Account', 'Employee Management', '202512222', 'Kenneth'),
('2026-03-23', '04:22:13', 'Create Account', 'Employee Management', '2026291', 'Kenneth'),
('2026-03-23', '04:25:09', 'Create Account', 'Employee Management', '2600163', 'Kenneth'),
('2026-03-23', '04:26:36', 'Create Account', 'Employee Management', '2600165', 'Kenneth'),
('2026-03-23', '04:27:50', 'Create Account', 'Employee Management', '202512221', 'Kenneth'),
('2026-03-23', '04:29:24', 'Create Account', 'Employee Management', '350165484', 'admin'),
('2026-03-23', '04:29:34', 'Create Account', 'Employee Management', '202512091', 'Kenneth'),
('2026-03-23', '04:33:30', 'Create Account', 'Employee Management', '350165486', 'admin'),
('2026-03-23', '04:34:48', 'Create Account', 'Employee Management', '230006075', 'Kenneth'),
('2026-03-23', '04:36:12', 'Create Account', 'Employee Management', '230006067', 'Kenneth'),
('2026-03-23', '04:36:38', 'Create Account', 'Employee Management', '350165478', 'admin'),
('2026-03-23', '04:37:23', 'Create Account', 'Employee Management', '230006059', 'Kenneth'),
('2026-03-23', '04:38:24', 'Create Account', 'Employee Management', '350174677', 'admin'),
('2026-03-23', '04:38:36', 'Create Account', 'Employee Management', '230006032', 'Kenneth'),
('2026-03-23', '04:39:39', 'Create Account', 'Employee Management', '230006016', 'Kenneth'),
('2026-03-23', '04:39:56', 'Create Account', 'Employee Management', '350174681', 'admin'),
('2026-03-23', '04:40:43', 'Create Account', 'Employee Management', '230006008', 'Kenneth'),
('2026-03-23', '04:41:25', 'Create Account', 'Employee Management', '350188592', 'admin'),
('2026-03-23', '04:41:45', 'Create Account', 'Employee Management', '230005966', 'Kenneth'),
('2026-03-23', '04:42:51', 'Create Account', 'Employee Management', '202502071', 'admin'),
('2026-03-23', '04:42:56', 'Create Account', 'Employee Management', '230005826', 'Kenneth'),
('2026-03-23', '04:48:51', 'Create Account', 'Employee Management', '230004935', 'Kenneth'),
('2026-03-23', '04:50:31', 'Create Account', 'Employee Management', '230005052', 'Kenneth'),
('2026-03-23', '04:51:54', 'Create Account', 'Employee Management', '230005087', 'Kenneth'),
('2026-03-23', '04:53:36', 'Create Account', 'Employee Management', '230005273', 'Kenneth'),
('2026-03-23', '04:54:54', 'Create Account', 'Employee Management', '230005206', 'Kenneth'),
('2026-03-23', '04:56:06', 'Create Account', 'Employee Management', '230005958', 'Kenneth'),
('2026-03-23', '04:57:47', 'Create Account', 'Employee Management', '202503171', 'Kenneth'),
('2026-03-23', '04:59:16', 'Create Account', 'Employee Management', '202503241', 'Kenneth'),
('2026-03-23', '05:00:51', 'Create Account', 'Employee Management', '202503251', 'Kenneth'),
('2026-03-23', '05:02:20', 'Create Account', 'Employee Management', '202504031', 'Kenneth'),
('2026-03-23', '05:03:36', 'Create Account', 'Employee Management', '202504141', 'Kenneth'),
('2026-03-23', '20:17:23', 'Create Account', 'Employee Management', '20251211', 'Kenneth'),
('2026-03-23', '20:18:33', 'Create Account', 'Employee Management', '20251212', 'Kenneth'),
('2026-03-23', '20:20:10', 'Create Account', 'Employee Management', '202511171', 'Kenneth'),
('2026-03-23', '20:21:32', 'Create Account', 'Employee Management', '202511172', 'Kenneth'),
('2026-03-23', '20:23:07', 'Create Account', 'Employee Management', '202511102', 'Kenneth'),
('2026-03-23', '20:24:18', 'Create Account', 'Employee Management', '202510272', 'Kenneth'),
('2026-03-23', '20:25:46', 'Create Account', 'Employee Management', '202510241', 'Kenneth'),
('2026-03-23', '20:27:38', 'Create Account', 'Employee Management', '202510202', 'Kenneth'),
('2026-03-23', '20:28:56', 'Create Account', 'Employee Management', '202510201', 'Kenneth'),
('2026-03-23', '20:30:15', 'Create Account', 'Employee Management', '202510062', 'Kenneth'),
('2026-03-23', '20:31:39', 'Create Account', 'Employee Management', '350120040', 'Kenneth'),
('2026-03-23', '20:34:02', 'Create Account', 'Employee Management', '350150184', 'Kenneth'),
('2026-03-23', '20:35:30', 'Create Account', 'Employee Management', '202504282', 'Kenneth'),
('2026-03-23', '20:41:10', 'Create Account', 'Employee Management', '202507213', 'Kenneth'),
('2026-03-23', '20:42:21', 'Create Account', 'Employee Management', '202507214', 'Kenneth'),
('2026-03-23', '20:43:35', 'Create Account', 'Employee Management', '202507281', 'Kenneth'),
('2026-03-23', '20:44:46', 'Create Account', 'Employee Management', '202508111', 'Kenneth'),
('2026-03-23', '20:46:22', 'Create Account', 'Employee Management', '202508181', 'Kenneth'),
('2026-03-23', '20:47:28', 'Create Account', 'Employee Management', '202509151', 'Kenneth'),
('2026-03-23', '20:48:35', 'Create Account', 'Employee Management', '202509153', 'Kenneth'),
('2026-03-23', '20:50:24', 'Create Account', 'Employee Management', '202510061', 'Kenneth'),
('2026-03-23', '20:54:51', 'Updated Account', 'Employee Management', '2026224', 'Kenneth'),
('2026-03-23', '21:00:02', 'Updated Account', 'Employee Management', '230004935', 'Kenneth'),
('2026-03-23', '21:00:36', 'Updated Account', 'Employee Management', '230004943', 'Kenneth'),
('2026-03-23', '21:05:12', 'Updated Account', 'Employee Management', '230005109', 'Kenneth'),
('2026-03-23', '21:06:26', 'Updated Account', 'Employee Management', '230005052', 'Kenneth'),
('2026-03-23', '21:06:54', 'Updated Account', 'Employee Management', '230005087', 'Kenneth'),
('2026-03-23', '21:07:19', 'Updated Account', 'Employee Management', '230005133', 'Kenneth'),
('2026-03-23', '21:07:42', 'Updated Account', 'Employee Management', '230005141', 'Kenneth'),
('2026-03-23', '21:08:07', 'Updated Account', 'Employee Management', '230005206', 'Kenneth'),
('2026-03-23', '21:08:35', 'Updated Account', 'Employee Management', '230005265', 'Kenneth'),
('2026-03-23', '21:09:02', 'Updated Account', 'Employee Management', '230005273', 'Kenneth'),
('2026-03-23', '21:09:33', 'Updated Account', 'Employee Management', '230005338', 'Kenneth'),
('2026-03-23', '21:10:02', 'Updated Account', 'Employee Management', '230005486', 'Kenneth'),
('2026-03-23', '21:10:25', 'Updated Account', 'Employee Management', '230005575', 'Kenneth'),
('2026-03-23', '21:10:48', 'Updated Account', 'Employee Management', '230005613', 'Kenneth'),
('2026-03-23', '21:11:06', 'Updated Account', 'Employee Management', '230005621', 'Kenneth'),
('2026-03-23', '21:11:29', 'Updated Account', 'Employee Management', '230005656', 'Kenneth'),
('2026-03-23', '21:13:01', 'Updated Account', 'Employee Management', '230005826', 'Kenneth'),
('2026-03-23', '21:13:19', 'Updated Account', 'Employee Management', '230005958', 'Kenneth'),
('2026-03-23', '21:13:41', 'Updated Account', 'Employee Management', '230005966', 'Kenneth'),
('2026-03-23', '21:14:01', 'Updated Account', 'Employee Management', '230006008', 'Kenneth'),
('2026-03-23', '21:14:21', 'Updated Account', 'Employee Management', '230006016', 'Kenneth'),
('2026-03-23', '21:14:44', 'Updated Account', 'Employee Management', '230006032', 'Kenneth'),
('2026-03-23', '21:15:16', 'Updated Account', 'Employee Management', '230006059', 'Kenneth'),
('2026-03-23', '21:15:36', 'Updated Account', 'Employee Management', '230006067', 'Kenneth'),
('2026-03-23', '21:16:02', 'Updated Account', 'Employee Management', '230006075', 'Kenneth'),
('2026-03-23', '21:16:30', 'Updated Account', 'Employee Management', '350120040', 'Kenneth'),
('2026-03-23', '21:16:55', 'Updated Account', 'Employee Management', '350136175', 'Kenneth'),
('2026-03-23', '21:17:15', 'Updated Account', 'Employee Management', '350146257', 'Kenneth'),
('2026-03-23', '21:17:30', 'Updated Account', 'Employee Management', '350150184', 'Kenneth'),
('2026-03-23', '21:18:05', 'Updated Account', 'Employee Management', '350150170', 'Kenneth'),
('2026-03-23', '21:18:24', 'Updated Account', 'Employee Management', '350081936', 'Kenneth'),
('2026-03-23', '21:18:41', 'Updated Account', 'Employee Management', '350165484', 'Kenneth'),
('2026-03-23', '21:19:01', 'Updated Account', 'Employee Management', '350165486', 'Kenneth'),
('2026-03-23', '21:19:29', 'Updated Account', 'Employee Management', '350165486', 'Kenneth'),
('2026-03-23', '21:20:10', 'Updated Account', 'Employee Management', '350174677', 'Kenneth'),
('2026-03-23', '21:20:40', 'Updated Account', 'Employee Management', '350174681', 'Kenneth'),
('2026-03-23', '21:20:59', 'Updated Account', 'Employee Management', '350188592', 'Kenneth'),
('2026-03-23', '21:21:17', 'Updated Account', 'Employee Management', '202502071', 'Kenneth'),
('2026-03-23', '21:21:37', 'Updated Account', 'Employee Management', '202503171', 'Kenneth'),
('2026-03-23', '21:22:03', 'Updated Account', 'Employee Management', '202503241', 'Kenneth'),
('2026-03-23', '21:22:24', 'Updated Account', 'Employee Management', '202503251', 'Kenneth'),
('2026-03-23', '21:22:43', 'Updated Account', 'Employee Management', '202504031', 'Kenneth'),
('2026-03-23', '21:23:01', 'Updated Account', 'Employee Management', '202504141', 'Kenneth'),
('2026-03-23', '21:23:25', 'Updated Account', 'Employee Management', '202504282', 'Kenneth'),
('2026-03-23', '21:26:04', 'Create Account', 'Employee Management', '202506031', 'Kenneth'),
('2026-03-23', '21:27:12', 'Updated Account', 'Employee Management', '202507213', 'Kenneth'),
('2026-03-23', '21:27:33', 'Updated Account', 'Employee Management', '202507214', 'Kenneth'),
('2026-03-23', '21:27:49', 'Updated Account', 'Employee Management', '202507281', 'Kenneth'),
('2026-03-23', '21:28:08', 'Updated Account', 'Employee Management', '202508111', 'Kenneth'),
('2026-03-23', '21:28:27', 'Updated Account', 'Employee Management', '202508181', 'Kenneth'),
('2026-03-23', '21:28:47', 'Updated Account', 'Employee Management', '202509151', 'Kenneth'),
('2026-03-23', '21:29:06', 'Updated Account', 'Employee Management', '202509153', 'Kenneth'),
('2026-03-23', '21:29:29', 'Updated Account', 'Employee Management', '202510061', 'Kenneth'),
('2026-03-23', '21:30:02', 'Updated Account', 'Employee Management', '202510062', 'Kenneth'),
('2026-03-23', '21:30:26', 'Updated Account', 'Employee Management', '202510201', 'Kenneth'),
('2026-03-23', '21:31:04', 'Updated Account', 'Employee Management', '202510202', 'Kenneth'),
('2026-03-23', '21:31:32', 'Updated Account', 'Employee Management', '202510241', 'Kenneth'),
('2026-03-23', '21:31:59', 'Updated Account', 'Employee Management', '202510272', 'Kenneth'),
('2026-03-23', '21:32:17', 'Updated Account', 'Employee Management', '202511102', 'Kenneth'),
('2026-03-23', '21:32:42', 'Updated Account', 'Employee Management', '202511172', 'Kenneth'),
('2026-03-23', '21:33:06', 'Updated Account', 'Employee Management', '202511171', 'Kenneth'),
('2026-03-23', '21:33:21', 'Updated Account', 'Employee Management', '20251212', 'Kenneth'),
('2026-03-23', '21:33:39', 'Updated Account', 'Employee Management', '20251211', 'Kenneth'),
('2026-03-23', '21:34:06', 'Updated Account', 'Employee Management', '202512091', 'Kenneth'),
('2026-03-23', '21:34:22', 'Updated Account', 'Employee Management', '202512221', 'Kenneth'),
('2026-03-23', '21:34:47', 'Updated Account', 'Employee Management', '202512222', 'Kenneth'),
('2026-03-23', '21:35:04', 'Updated Account', 'Employee Management', '20261191', 'Kenneth'),
('2026-03-23', '21:35:19', 'Updated Account', 'Employee Management', '2026221', 'Kenneth'),
('2026-03-23', '21:35:40', 'Updated Account', 'Employee Management', '2026222', 'Kenneth'),
('2026-03-23', '21:36:03', 'Updated Account', 'Employee Management', '2026224', 'Kenneth'),
('2026-03-23', '21:36:33', 'Updated Account', 'Employee Management', '2026261', 'Kenneth'),
('2026-03-23', '21:36:51', 'Updated Account', 'Employee Management', '2026262', 'Kenneth'),
('2026-03-23', '21:37:15', 'Updated Account', 'Employee Management', '2026292', 'Kenneth'),
('2026-03-23', '21:37:33', 'Updated Account', 'Employee Management', '2026291', 'Kenneth'),
('2026-03-23', '21:37:52', 'Updated Account', 'Employee Management', '2600163', 'Kenneth'),
('2026-03-23', '21:38:16', 'Updated Account', 'Employee Management', '1072025', 'Kenneth'),
('2026-03-23', '21:39:01', 'Updated Account', 'Employee Management', '2600165', 'Kenneth'),
('2026-03-24', '04:19:28', 'Updated Account', 'Employee Management', '1072025', 'admin'),
('2026-03-24', '04:23:25', 'Updated Account', 'Employee Management', '230004935', 'kennethdador'),
('2026-03-24', '04:23:58', 'Updated Account', 'Employee Management', '230004943', 'kennethdador'),
('2026-03-24', '04:24:34', 'Updated Account', 'Employee Management', '230005109', 'kennethdador'),
('2026-03-24', '04:25:04', 'Updated Account', 'Employee Management', '230005052', 'kennethdador'),
('2026-03-24', '04:25:40', 'Updated Account', 'Employee Management', '230005087', 'kennethdador'),
('2026-03-24', '04:26:07', 'Updated Account', 'Employee Management', '230005133', 'kennethdador'),
('2026-03-24', '04:26:50', 'Updated Account', 'Employee Management', '230005141', 'kennethdador'),
('2026-03-24', '04:27:37', 'Updated Account', 'Employee Management', '230005206', 'kennethdador'),
('2026-03-24', '04:28:14', 'Updated Account', 'Employee Management', '230005265', 'kennethdador'),
('2026-03-24', '04:30:12', 'Updated Account', 'Employee Management', '230005273', 'kennethdador'),
('2026-03-24', '04:30:57', 'Updated Account', 'Employee Management', '230005338', 'kennethdador'),
('2026-03-24', '04:36:49', 'Updated Account', 'Employee Management', '230005486', 'kennethdador'),
('2026-03-24', '04:37:21', 'Updated Account', 'Employee Management', '230005575', 'kennethdador'),
('2026-03-24', '04:37:56', 'Updated Account', 'Employee Management', '230005613', 'kennethdador'),
('2026-03-24', '04:38:23', 'Updated Account', 'Employee Management', '230005621', 'kennethdador'),
('2026-03-24', '04:38:50', 'Updated Account', 'Employee Management', '230005656', 'kennethdador'),
('2026-03-24', '04:39:38', 'Updated Account', 'Employee Management', '2026261', 'kennethdador'),
('2026-03-24', '04:40:04', 'Updated Account', 'Employee Management', '2026262', 'kennethdador'),
('2026-03-24', '04:40:27', 'Updated Account', 'Employee Management', '2026292', 'kennethdador'),
('2026-03-24', '04:40:48', 'Updated Account', 'Employee Management', '2026291', 'kennethdador'),
('2026-03-24', '04:41:08', 'Updated Account', 'Employee Management', '2600163', 'kennethdador'),
('2026-03-24', '04:41:44', 'Updated Account', 'Employee Management', '1072025', 'kennethdador'),
('2026-03-24', '04:42:11', 'Updated Account', 'Employee Management', '2600165', 'kennethdador'),
('2026-03-24', '04:42:48', 'Updated Account', 'Employee Management', '2026224', 'kennethdador'),
('2026-03-24', '04:43:10', 'Updated Account', 'Employee Management', '2026222', 'kennethdador'),
('2026-03-24', '04:43:46', 'Updated Account', 'Employee Management', '2026221', 'kennethdador'),
('2026-03-24', '04:44:08', 'Updated Account', 'Employee Management', '20261191', 'kennethdador'),
('2026-03-24', '04:44:32', 'Updated Account', 'Employee Management', '202512222', 'kennethdador'),
('2026-03-24', '04:44:59', 'Updated Account', 'Employee Management', '202512221', 'kennethdador'),
('2026-03-24', '04:45:26', 'Updated Account', 'Employee Management', '202512091', 'kennethdador'),
('2026-03-24', '04:45:54', 'Updated Account', 'Employee Management', '20251211', 'kennethdador'),
('2026-03-24', '04:46:21', 'Updated Account', 'Employee Management', '20251212', 'kennethdador'),
('2026-03-24', '04:46:54', 'Updated Account', 'Employee Management', '202511171', 'kennethdador'),
('2026-03-24', '04:47:59', 'Updated Account', 'Employee Management', '202510062', 'kennethdador'),
('2026-03-24', '04:48:20', 'Updated Account', 'Employee Management', '202510201', 'kennethdador'),
('2026-03-24', '04:48:41', 'Updated Account', 'Employee Management', '202510202', 'kennethdador'),
('2026-03-24', '04:49:08', 'Updated Account', 'Employee Management', '202510241', 'kennethdador'),
('2026-03-24', '04:49:33', 'Updated Account', 'Employee Management', '202510272', 'kennethdador'),
('2026-03-24', '04:50:43', 'Updated Account', 'Employee Management', '202511102', 'kennethdador'),
('2026-03-24', '04:51:07', 'Updated Account', 'Employee Management', '202511172', 'kennethdador'),
('2026-03-24', '04:51:55', 'Updated Account', 'Employee Management', '202510061', 'kennethdador'),
('2026-03-24', '04:52:20', 'Updated Account', 'Employee Management', '202509153', 'kennethdador'),
('2026-03-24', '04:52:42', 'Updated Account', 'Employee Management', '202509151', 'kennethdador'),
('2026-03-24', '04:53:06', 'Updated Account', 'Employee Management', '202508181', 'kennethdador'),
('2026-03-24', '04:53:29', 'Updated Account', 'Employee Management', '202508111', 'kennethdador'),
('2026-03-24', '04:54:24', 'Updated Account', 'Employee Management', '202507281', 'kennethdador'),
('2026-03-24', '04:54:51', 'Updated Account', 'Employee Management', '202507214', 'kennethdador'),
('2026-03-24', '04:55:35', 'Updated Account', 'Employee Management', '202507213', 'kennethdador'),
('2026-03-24', '04:56:04', 'Updated Account', 'Employee Management', '202506031', 'kennethdador'),
('2026-03-24', '04:56:28', 'Updated Account', 'Employee Management', '202504282', 'kennethdador'),
('2026-03-24', '04:56:51', 'Updated Account', 'Employee Management', '202504141', 'kennethdador'),
('2026-03-24', '04:57:23', 'Updated Account', 'Employee Management', '202504031', 'kennethdador'),
('2026-03-24', '04:57:54', 'Updated Account', 'Employee Management', '202503251', 'kennethdador'),
('2026-03-24', '04:58:22', 'Updated Account', 'Employee Management', '202503241', 'kennethdador'),
('2026-03-24', '04:58:59', 'Updated Account', 'Employee Management', '350174681', 'kennethdador'),
('2026-03-24', '04:59:21', 'Updated Account', 'Employee Management', '350188592', 'kennethdador'),
('2026-03-24', '04:59:52', 'Updated Account', 'Employee Management', '202502071', 'kennethdador'),
('2026-03-24', '05:00:12', 'Updated Account', 'Employee Management', '202503171', 'kennethdador'),
('2026-03-24', '21:02:52', 'Updated Account', 'Employee Management', '350136175', 'admin'),
('2026-03-24', '21:11:14', 'Updated Account', 'Employee Management', '350150184', 'admin'),
('2026-03-24', '21:11:57', 'Updated Account', 'Employee Management', '350120040', 'admin'),
('2026-03-24', '21:12:36', 'Updated Account', 'Employee Management', '230005958', 'admin'),
('2026-03-24', '21:13:17', 'Updated Account', 'Employee Management', '230005826', 'admin'),
('2026-03-24', '21:36:43', 'Updated Account', 'Employee Management', '230005966', 'admin'),
('2026-03-24', '21:37:23', 'Updated Account', 'Employee Management', '230006008', 'admin'),
('2026-03-24', '21:37:46', 'Updated Account', 'Employee Management', '230006016', 'admin'),
('2026-03-24', '21:38:05', 'Updated Account', 'Employee Management', '230006032', 'admin'),
('2026-03-24', '21:38:21', 'Updated Account', 'Employee Management', '350174677', 'admin'),
('2026-03-24', '21:39:00', 'Updated Account', 'Employee Management', '230006059', 'admin'),
('2026-03-24', '21:39:23', 'Updated Account', 'Employee Management', '350165478', 'admin'),
('2026-03-24', '21:39:49', 'Updated Account', 'Employee Management', '230006067', 'admin'),
('2026-03-24', '21:40:11', 'Updated Account', 'Employee Management', '230006075', 'admin'),
('2026-03-24', '21:40:36', 'Updated Account', 'Employee Management', '350165486', 'admin'),
('2026-03-24', '21:41:23', 'Updated Account', 'Employee Management', '350165484', 'admin'),
('2026-03-24', '21:47:28', 'Updated Account', 'Employee Management', '350081936', 'admin'),
('2026-03-24', '21:53:17', 'Updated Account', 'Employee Management', '350150170', 'admin'),
('2026-03-24', '21:55:03', 'Updated Account', 'Employee Management', '350146257', 'admin'),
('2026-03-26', '01:08:07', 'Update Group', 'Group Management', 'DERE', 'admin'),
('2026-03-26', '01:16:09', 'Add Branch', 'Branch Management', 'Bakal', 'admin'),
('2026-03-26', '01:17:50', 'Add Group', 'Group Management', 'DEREdd', 'admin'),
('2026-03-26', '01:46:55', 'Add Asset', 'Asset Inventory', 'Asset ID: 25', 'admin'),
('2026-03-26', '01:47:37', 'Transferred asset OE-15001 to Santos, Alvin Abad (RVG)', 'Asset Inventory', '25', '2200426'),
('2026-03-26', '01:47:37', 'Transfer Asset', 'Asset Inventory', '350150184', 'admin'),
('2026-03-26', '01:48:21', 'Create', 'Ticket Management', '86', 'storagemart.alvinsan'),
('2026-03-26', '01:49:29', 'Approve & Assign', 'Ticket Management', '86', 'admin'),
('2026-03-26', '01:49:49AM', 'Reassigned Ticket', 'Ticket Management', '86', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `tbltickets`
--

CREATE TABLE `tbltickets` (
  `ticket_id` int(11) NOT NULL,
  `ticket_number` varchar(50) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `concern_details` text,
  `priority` enum('Low','Medium','High') DEFAULT 'Low',
  `status` enum('Pending','In Progress','On Hold','Resolved','Closed','Reopened','Unresolved','Decline','Approve') DEFAULT 'Pending',
  `remarks` text,
  `assigned_to` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `date_approved` datetime DEFAULT NULL,
  `declined_by` int(11) DEFAULT NULL,
  `date_declined` datetime DEFAULT NULL,
  `decline_reason` text,
  `date_filed` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbltickets`
--

INSERT INTO `tbltickets` (`ticket_id`, `ticket_number`, `employee_id`, `inventory_id`, `branch_id`, `department`, `category`, `concern_details`, `priority`, `status`, `remarks`, `assigned_to`, `approved_by`, `date_approved`, `declined_by`, `date_declined`, `decline_reason`, `date_filed`, `last_updated`, `created_by`) VALUES
(86, 'STM-20260326-0086', 350150184, 25, 20, 'Operations', 'Network', 'ddaa', 'Low', 'Resolved', 'dad', 202501071, 2200426, '2026-03-26 01:49:29', NULL, NULL, NULL, '2026-03-26 01:48:21', '2026-03-26 01:51:30', 2200562);

-- --------------------------------------------------------

--
-- Table structure for table `tblticket_history`
--

CREATE TABLE `tblticket_history` (
  `history_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `action_type` enum('Approved','Created','Assigned','Updated','Resolved','Reopened','Closed','On Hold','Unresolved') DEFAULT 'Updated',
  `action_details` text,
  `old_status` enum('Pending','In Progress','On Hold','Resolved','Closed','Reopened','Unresolved') DEFAULT NULL,
  `new_status` enum('Pending','In Progress','On Hold','Resolved','Closed','Reopened','Unresolved') DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `performed_role` varchar(50) DEFAULT NULL,
  `date_logged` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblticket_history`
--

INSERT INTO `tblticket_history` (`history_id`, `ticket_id`, `action_type`, `action_details`, `old_status`, `new_status`, `performed_by`, `performed_role`, `date_logged`) VALUES
(121, 86, 'Created', 'Ticket filed by employee', NULL, 'Pending', 350150184, 'Employee', '2026-03-26 01:48:21'),
(122, 86, 'Approved', 'Approved & assigned to employee 1072025', 'Pending', 'In Progress', 2200426, 'Admin', '2026-03-26 01:49:29'),
(123, 86, '', 'Reassigned from Kenneth Dador to Kenneth Dador', 'In Progress', 'In Progress', 2200425, 'IT Staff', '2026-03-26 01:49:49'),
(124, 86, 'Resolved', 'Ticket Resolved by IT Staff (Account ID: 2200428)', 'In Progress', 'Resolved', 2200428, 'IT Staff', '2026-03-26 01:51:30');

-- --------------------------------------------------------

--
-- Table structure for table `tblticket_technical`
--

CREATE TABLE `tblticket_technical` (
  `tech_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `performed_by` int(11) NOT NULL,
  `technical_purpose` varchar(255) DEFAULT NULL,
  `action_taken` text,
  `result` text,
  `date_performed` datetime DEFAULT CURRENT_TIMESTAMP,
  `remarks` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblticket_technical`
--

INSERT INTO `tblticket_technical` (`tech_id`, `ticket_id`, `performed_by`, `technical_purpose`, `action_taken`, `result`, `date_performed`, `remarks`) VALUES
(29, 86, 202501071, 'Network Issue', 'ada', 'dadadadda', '2026-03-26 01:51:30', 'dad');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_ratings`
--

CREATE TABLE `ticket_ratings` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `it_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ticket_ratings`
--

INSERT INTO `ticket_ratings` (`id`, `ticket_id`, `employee_id`, `it_id`, `rating`, `comment`, `created_at`) VALUES
(12, 86, 350150184, 202501071, 4, '', '2026-03-26 01:53:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblaccounts`
--
ALTER TABLE `tblaccounts`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `tblassets_assignment`
--
ALTER TABLE `tblassets_assignment`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `employeeID` (`employee_id`),
  ADD KEY `tblassignment_fk_inventoryID` (`inventory_id`);

--
-- Indexes for table `tblassets_category`
--
ALTER TABLE `tblassets_category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `tblassets_directory`
--
ALTER TABLE `tblassets_directory`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `tblassets_group`
--
ALTER TABLE `tblassets_group`
  ADD PRIMARY KEY (`group_id`),
  ADD KEY `assetCategory_id` (`category_id`);

--
-- Indexes for table `tblassets_inventory`
--
ALTER TABLE `tblassets_inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `assetEmployee_id` (`employee_id`),
  ADD KEY `fk_inventory_branch` (`branch_id`),
  ADD KEY `fk_inventory_group` (`group_id`);

--
-- Indexes for table `tblbranch`
--
ALTER TABLE `tblbranch`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `tblemployee`
--
ALTER TABLE `tblemployee`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `fk_branch_id` (`branch_id`);

--
-- Indexes for table `tbltickets`
--
ALTER TABLE `tbltickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `inventory_id` (`inventory_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `tblticket_history`
--
ALTER TABLE `tblticket_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `tblticket_technical`
--
ALTER TABLE `tblticket_technical`
  ADD PRIMARY KEY (`tech_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `ticket_ratings`
--
ALTER TABLE `ticket_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_id` (`ticket_id`,`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tblaccounts`
--
ALTER TABLE `tblaccounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2200573;

--
-- AUTO_INCREMENT for table `tblassets_assignment`
--
ALTER TABLE `tblassets_assignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `tblassets_category`
--
ALTER TABLE `tblassets_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tblassets_directory`
--
ALTER TABLE `tblassets_directory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblassets_group`
--
ALTER TABLE `tblassets_group`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tblassets_inventory`
--
ALTER TABLE `tblassets_inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `tblbranch`
--
ALTER TABLE `tblbranch`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tbltickets`
--
ALTER TABLE `tbltickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `tblticket_history`
--
ALTER TABLE `tblticket_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `tblticket_technical`
--
ALTER TABLE `tblticket_technical`
  MODIFY `tech_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `ticket_ratings`
--
ALTER TABLE `ticket_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblassets_assignment`
--
ALTER TABLE `tblassets_assignment`
  ADD CONSTRAINT `employeeID` FOREIGN KEY (`employee_id`) REFERENCES `tblemployee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tblassignment_fk_inventoryID` FOREIGN KEY (`inventory_id`) REFERENCES `tblassets_inventory` (`inventory_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tblassets_directory`
--
ALTER TABLE `tblassets_directory`
  ADD CONSTRAINT `category_id` FOREIGN KEY (`category_id`) REFERENCES `tblassets_category` (`category_id`) ON UPDATE CASCADE;

--
-- Constraints for table `tblassets_group`
--
ALTER TABLE `tblassets_group`
  ADD CONSTRAINT `fk_group_category` FOREIGN KEY (`category_id`) REFERENCES `tblassets_category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tblassets_inventory`
--
ALTER TABLE `tblassets_inventory`
  ADD CONSTRAINT `assetEmployee_id` FOREIGN KEY (`employee_id`) REFERENCES `tblemployee` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `assignment_id` FOREIGN KEY (`assignment_id`) REFERENCES `tblassets_assignment` (`assignment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inventory_branch` FOREIGN KEY (`branch_id`) REFERENCES `tblbranch` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inventory_group` FOREIGN KEY (`group_id`) REFERENCES `tblassets_group` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tblemployee`
--
ALTER TABLE `tblemployee`
  ADD CONSTRAINT `account_id` FOREIGN KEY (`account_id`) REFERENCES `tblaccounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `tblbranch` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbltickets`
--
ALTER TABLE `tbltickets`
  ADD CONSTRAINT `tbltickets_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `tblemployee` (`employee_id`),
  ADD CONSTRAINT `tbltickets_ibfk_2` FOREIGN KEY (`inventory_id`) REFERENCES `tblassets_inventory` (`inventory_id`),
  ADD CONSTRAINT `tbltickets_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `tblemployee` (`employee_id`);

--
-- Constraints for table `tblticket_history`
--
ALTER TABLE `tblticket_history`
  ADD CONSTRAINT `tblticket_history_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tbltickets` (`ticket_id`) ON DELETE CASCADE;

--
-- Constraints for table `tblticket_technical`
--
ALTER TABLE `tblticket_technical`
  ADD CONSTRAINT `tblticket_technical_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tbltickets` (`ticket_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblticket_technical_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `tblemployee` (`employee_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
